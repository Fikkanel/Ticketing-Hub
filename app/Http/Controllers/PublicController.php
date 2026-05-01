<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Location;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Discount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail; 
use App\Mail\OrderConfirmation;      
use App\Models\Banner;
use App\Models\Bundle;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Hash;      
use App\Models\WaitingRoomEntry;

class PublicController extends Controller
{
    public function index(Request $request, $slug = null)
    {
        // 1. Ambil Data Lokasi untuk Filter
        $locations = Location::all();
        
        // Ambil semua kategori untuk filter (exclude Free karena itu otomatis)
        $categories = \App\Models\Category::where('name', '!=', 'Free')->orderBy('name')->get();

        // 2. Ambil Banner Utama (Aktif)
        $banners = Banner::where('is_active', true)->orderBy('order')->get();

        // Resolve category: prioritaskan slug dari URL, fallback ke query param
        $categoryFilter = $slug ?? $request->category;

        // 3. Query Event Dasar (Hanya yang Aktif & Masa Depan/Sekarang)
        $query = Event::with(['location', 'products' => function($q) {
                            $q->select('product_id', 'event_id', 'harga')->where('is_sponsorship', false);
                       }, 'categories'])
                       ->whereIn('status', ['Upcoming', 'Active', 'Finished'])
                       ->orderBy('tgl_mulai', 'desc');

        // Filter by Search Query (Q)
        if ($request->has('q') && !empty($request->q)) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        // Filter by Location
        if ($request->has('location') && !empty($request->location)) {
            $query->where('location_id', $request->location);
        }

        // Filter by Date (Start Date)
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('tgl_mulai', $request->date);
        }
        
        // Filter by Category
        if (!empty($categoryFilter)) {
            if ($categoryFilter === 'free') {
                // Filter FREE: event dimana semua produk harganya 0
                $query->whereHas('products')
                      ->whereDoesntHave('products', function($q) {
                          $q->where('harga', '>', 0);
                      });
            } else {
                // Cari berdasarkan slug atau ID
                $category = \App\Models\Category::where('slug', $categoryFilter)
                    ->orWhere('id', $categoryFilter)
                    ->first();
                
                if ($category) {
                    $query->whereHas('categories', function($q) use ($category) {
                        $q->where('categories.id', $category->id);
                    });
                    $categoryFilter = $category->slug; // normalize to slug
                }
            }
        }

        $events = $query->get();
        $locations = Location::all();
        
        // Active category untuk highlight
        $activeCategory = $categoryFilter;
        
        return view('public.index', compact('events', 'locations', 'banners', 'categories', 'activeCategory'));
    }

    public function showEventDetail($event_id)
    {
        $event = Event::with([
            'location', 
            'products' => function($q) {
                $q->where('is_sponsorship', false)->with('seatLayout.seats');
            }, 
            'organizers', 
            'bundles.items.product'
        ])->findOrFail($event_id);
        return view('public.event_detail', compact('event'));
    }

    /**
     * Tampilkan halaman profil organizer publik.
     */
    public function showOrganizerProfile($slug)
    {
        $organizer = \App\Models\User::where('organizer_slug', $slug)
            ->whereNotNull('organizer_name')
            ->firstOrFail();

        // Ambil semua event yang dikelola organizer ini
        $events = $organizer->events()
            ->with(['location', 'products' => function($q) {
                $q->select('product_id', 'event_id', 'harga')->where('is_sponsorship', false);
            }])
            ->whereIn('status', ['Upcoming', 'Active', 'Finished'])
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        return view('public.organizer_profile', compact('organizer', 'events'));
    }
    
    public function showCart()
    {
        return view('public.cart');
    }

    /**
     * Cek apakah email sudah terdaftar (AJAX)
     */
    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $email = strtolower(trim($request->email));
        $customer = Customer::where('email', $email)->first();
        
        return response()->json([
            'exists' => (bool)$customer,
            'name' => $customer ? $customer->name : null,
        ]);
    }

    public function showCheckoutForm(Request $request)
    {
        // Ambil Settingan Pajak
        $taxSettings = [
            'admin_fee_percentage' => \App\Models\Setting::get('tax_admin_fee_percentage', 3),
            'admin_fee_min' => \App\Models\Setting::get('tax_admin_fee_min', 2000),
            'ppn_percent' => \App\Models\Setting::get('tax_ppn_percentage', 11),
            'service_fee_va' => \App\Models\Setting::get('tax_service_fee_va', 4500),
            'service_fee_gopay' => \App\Models\Setting::get('tax_service_fee_gopay', 2000),
            'service_fee_qris' => \App\Models\Setting::get('tax_service_fee_qris', 0.7), // dalam persen
        ];

        // Cek apakah ini free checkout (semua produk gratis)
        // Ambil dari session atau request jika ada
        $isFreeCheckout = false;
        
        // Dapatkan cart dari request atau session jika memungkinkan
        // Untuk sekarang kita akan cek di frontend via JavaScript

        return view('public.checkout', compact('taxSettings', 'isFreeCheckout'));
    }
    
    public function validateDiscount(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'cart_items' => 'required|array',
            'total_amount' => 'nullable|numeric|min:0', // Made nullable - can calculate from cart
        ]);

        $code = $request->code;
        $cartItems = $request->cart_items;
        
        // Calculate total from cart if not provided
        $totalAmount = $request->total_amount;
        if ($totalAmount === null || $totalAmount === '') {
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += ($item['price'] ?? 0) * ($item['qty'] ?? 1);
            }
        }

        $discount = Discount::where('code', $code)
                            ->where('is_active', true)
                            ->whereRaw('used_count < max_uses')
                            ->where(function($query) {
                                $query->whereNull('expires_at')
                                      ->orWhere('expires_at', '>=', now());
                            })
                            ->first();
        
        if (!$discount) {
            return response()->json([
                'valid' => false,
                'message' => 'Kode tidak valid, kadaluarsa, atau sudah habis.',
                'discount_amount' => 0,
                'final_total' => $totalAmount,
            ], 404);
        }
        
        // Check if discount is event-specific
        if ($discount->event_id) {
            // Get product IDs from cart (extract regular product IDs)
            $productIds = [];
            foreach ($cartItems as $item) {
                $itemId = $item['id'] ?? null;
                if ($itemId && !str_starts_with((string)$itemId, 'bundle_')) {
                    $productIds[] = $itemId;
                }
            }
            
            // Check if any cart product belongs to the discount's event
            $validEventProduct = false;
            if (!empty($productIds)) {
                $validEventProduct = Product::whereIn('product_id', $productIds)
                    ->where('event_id', $discount->event_id)
                    ->exists();
            }
            
            if (!$validEventProduct) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Kode diskon ini hanya berlaku untuk event tertentu.',
                    'discount_amount' => 0,
                    'final_total' => $totalAmount,
                ], 400);
            }
        }
        
        $discountAmount = ($discount->percentage / 100) * $totalAmount;
        $finalTotal = max(0, $totalAmount - $discountAmount);

        return response()->json([
            'valid' => true,
            'message' => "Kode diskon ({$discount->percentage}%) berhasil diterapkan!",
            'discount_amount' => round($discountAmount),
            'final_total' => round($finalTotal),
            'code' => $discount->code,
        ]);
    }

    public function checkTaxStatus(Request $request)
    {
        try {
            $productIds = $request->input('product_ids', []);
            
            Log::info('Check Tax Status Request:', ['product_ids' => $productIds]);

            if (empty($productIds)) {
                return response()->json(['apply_tax' => true, 'is_free_cart' => false, 'payment_channels' => 'all']); 
            }

            // Separate bundle IDs from product IDs
            $bundleIds = [];
            $regularProductIds = [];
            
            foreach ($productIds as $id) {
                if (is_string($id) && strpos($id, 'bundle_') === 0) {
                    $bundleIds[] = (int) str_replace('bundle_', '', $id);
                } else {
                    $regularProductIds[] = $id;
                }
            }
            
            // Get products with their events
            $products = collect();
            if (!empty($regularProductIds)) {
                $products = Product::whereIn('product_id', $regularProductIds)->with('event')->get();
            }
            
            // Get bundles with their events
            $bundles = collect();
            if (!empty($bundleIds)) {
                $bundles = Bundle::whereIn('id', $bundleIds)->with('event')->get();
            }
            
            // Collect all events from products and bundles
            $events = $products->pluck('event')->merge($bundles->pluck('event'))->unique('event_id')->filter();
            
            // Check if ANY product/bundle belongs to a Regular event
            $hasRegular = $events->contains('payment_mode', 'regular');
            
            // Check if ALL items are FREE (price = 0)
            $productMaxPrice = $products->isNotEmpty() ? $products->max('harga') : 0;
            $bundleMaxPrice = $bundles->isNotEmpty() ? $bundles->max('price') : 0;
            $isFreeCart = ($products->count() + $bundles->count()) > 0 && max($productMaxPrice, $bundleMaxPrice) == 0;
            
            // Get payment channels from the first event (assuming single event checkout)
            // If any event has qris_only, use qris_only for safety
            $paymentChannels = 'all'; // default
            foreach ($events as $event) {
                if ($event->payment_channels === 'qris_only') {
                    $paymentChannels = 'qris_only';
                    break;
                }
            }
            
            // Determine allowed channels based on event setting
            if ($paymentChannels === 'qris_only') {
                $allowedChannels = ['qris'];
            } else {
                // All channels: VA, E-Wallet, QRIS
                $allowedChannels = ['bank_transfer', 'gopay', 'shopeepay', 'qris'];
            }
            
            // Merge buyer_form_fields from all events (OR logic - if any event requires a field, show it)
            $defaultBuyerFields = [
                ['field' => 'name', 'label' => 'Nama Lengkap', 'enabled' => true, 'required' => true],
                ['field' => 'email', 'label' => 'Email', 'enabled' => true, 'required' => true],
                ['field' => 'phone', 'label' => 'Nomor HP', 'enabled' => true, 'required' => true],
                ['field' => 'nik', 'label' => 'Nomor Identitas (NIK)', 'enabled' => false, 'required' => false],
                ['field' => 'dob', 'label' => 'Tanggal Lahir', 'enabled' => false, 'required' => false],
                ['field' => 'gender', 'label' => 'Jenis Kelamin', 'enabled' => false, 'required' => false],
            ];
            
            $mergedBuyerFields = $defaultBuyerFields;
            $mergedCustomFields = [];
            
            foreach ($events as $event) {
                // Get buyer fields from event
                $eventBuyerFields = $event->buyer_form_fields;
                if (is_string($eventBuyerFields)) {
                    $eventBuyerFields = json_decode($eventBuyerFields, true);
                }
                
                // If event has buyer_form_fields, merge with OR logic
                if (!empty($eventBuyerFields)) {
                    foreach ($eventBuyerFields as $eventField) {
                        foreach ($mergedBuyerFields as &$mergedField) {
                            if ($mergedField['field'] === $eventField['field']) {
                                // OR logic: if ANY event enables/requires the field, it's enabled/required
                                if (!empty($eventField['enabled'])) {
                                    $mergedField['enabled'] = true;
                                }
                                if (!empty($eventField['required'])) {
                                    $mergedField['required'] = true;
                                }
                            }
                        }
                    }
                }
                
                // Collect custom fields from all events
                $eventCustomFields = $event->custom_form_fields;
                if (is_string($eventCustomFields)) {
                    $eventCustomFields = json_decode($eventCustomFields, true);
                }
                if (!empty($eventCustomFields)) {
                    foreach ($eventCustomFields as $cf) {
                        // Avoid duplicates by checking label
                        $exists = collect($mergedCustomFields)->contains('label', $cf['label']);
                        if (!$exists) {
                            $mergedCustomFields[] = $cf;
                        }
                    }
                }
            }
            
            return response()->json([
                'apply_tax' => $hasRegular,
                'allowed_channels' => $allowedChannels,
                'payment_channels' => $paymentChannels,
                'is_free_cart' => $isFreeCart,
                'buyer_form_fields' => $mergedBuyerFields,
                'custom_form_fields' => $mergedCustomFields,
            ]);
        } catch (\Exception $e) {
            Log::error('Check Tax Status Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
        }
    }

    public function processCheckout(Request $request)
    {
        Log::info('Checkout Process Started', $request->all());

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'telepon' => 'required|string|max:15',
            'metode_pembayaran' => 'required|string|max:50',
            'password' => [
                'nullable',
                \Illuminate\Validation\Rule::requiredIf(function () {
                    return !Auth::guard('customer')->check();
                }),
                'string',
                'min:6'
            ],
            'diskon_code' => 'nullable|string|max:50',
            // 'cart_data_json' => 'required|json', // REMOVED: Insecure 
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        
        // SECURE: Read from Session
        $cartData = \Illuminate\Support\Facades\Session::get('cart_items', []);

        if (empty($cartData)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Keranjang belanja kosong!'], 400); 
            }
            return redirect()->route('public.cart.show')->with('error', 'Keranjang belanja kosong!');
        }

        DB::beginTransaction();

        try {
            // Customer Logic
            if (Auth::guard('customer')->check()) {
                // Scenario 1: Sudah Login
                $customer = Auth::guard('customer')->user();
                // Opsional: Update phone jika kosong
                if (empty($customer->phone) && $request->filled('telepon')) {
                    $customer->update(['phone' => $request->telepon]);
                }
            } else {
                // Scenario 2: Guest Checkout (Auto Register)
                $email = strtolower(trim($request->email));
                
                // Cek apakah email sudah ada
                $existingCustomer = Customer::where('email', $email)->first();
                
                if ($existingCustomer) {
                    // SECURITY PREVENTION: 
                    // Jika email sudah ada tapi user tidak login, kita TIDAK boleh memaksa order ke akun tersebut
                    // karena orang lain bisa sembarang pakai email orang.
                    // Frontend harusnya sudah memaksa login. Jika tembus ke sini, kita reject.
                    DB::rollBack();
                    return back()->with('error', 'Email sudah terdaftar. Silakan login terlebih dahulu untuk melanjutkan.')
                        ->withInput();
                }

                // Buat Akun Baru
                $customer = Customer::create([
                    'name' => $request->nama,
                    'email' => $email,
                    'phone' => $request->telepon,
                    'password' => Hash::make($request->password), // Password dari input
                    'email_verified_at' => now(), // Auto verify karena checkout dianggap validasi
                ]);

                // Login Otomatis
                Auth::guard('customer')->login($customer);
            }

            // Separate bundle IDs from product IDs
            $bundleIds = [];
            $regularProductIds = [];
            
            foreach ($cartData as $cartItem) {
                $id = $cartItem['id'];
                if (is_string($id) && strpos($id, 'bundle_') === 0) {
                    $bundleIds[] = (int) str_replace('bundle_', '', $id);
                } else {
                    $regularProductIds[] = $id;
                }
            }
            
            // Fetch Products WITH LOCK to prevent race condition (overselling)
            $productsDb = collect();
            if (!empty($regularProductIds)) {
                $productsDb = Product::with('event')->whereIn('product_id', $regularProductIds)->lockForUpdate()->get()->keyBy('product_id');
            }
            
            // Fetch Bundles WITH LOCK to prevent race condition (overselling)
            $bundlesDb = collect();
            if (!empty($bundleIds)) {
                $bundlesDb = Bundle::with('event', 'items.product')->whereIn('id', $bundleIds)->lockForUpdate()->get()->keyBy('id');
            }

            // =============================================
            // VALIDASI: 1 Email = 1 Transaksi
            // =============================================
            $email = strtolower(trim($request->email));
            
            // Collect all unique events from cart items
            $cartEvents = $productsDb->pluck('event')->merge($bundlesDb->pluck('event'))->unique('event_id')->filter();
            
            foreach ($cartEvents as $event) {
                // Check if this event has "1 Email = 1 Transaction" enabled
                if ($event->limit_one_email_per_transaction) {
                    // Check if this email already has a successful order for this event
                    $existingOrder = Order::whereHas('customer', function($q) use ($email) {
                            $q->where('email', $email);
                        })
                        ->whereHas('orderItems.product', function($q) use ($event) {
                            $q->where('event_id', $event->event_id);
                        })
                        ->whereIn('status', ['Paid', 'Completed', 'Pending']) // Count pending orders too
                        ->first();
                    
                    if ($existingOrder) {
                        DB::rollBack();
                        $errorMsg = 'Email ' . $email . ' sudah pernah melakukan pembelian untuk event "' . $event->judul . '". Setiap email hanya dapat melakukan 1 transaksi untuk event ini.';
                        
                        if ($request->wantsJson()) {
                            return response()->json([
                                'success' => false, 
                                'message' => $errorMsg
                            ], 422);
                        }
                        return back()->with('error', $errorMsg)->withInput();
                    }
                }
            }

            // Tax Logic Detection
            $applyTax = false;
            $totalHargaProduk = 0;
            $orderItemsData = [];
            
            // Check products for tax
            foreach ($productsDb as $p) {
                if ($p->event && $p->event->payment_mode === 'regular') {
                    $applyTax = true;
                    break; 
                }
            }
            // Check bundles for tax if not already found
            if (!$applyTax) {
                foreach ($bundlesDb as $b) {
                    if ($b->event && $b->event->payment_mode === 'regular') {
                        $applyTax = true;
                        break; 
                    }
                }
            }

            // Process cart items
            foreach ($cartData as $item) {
                $itemId = $item['id'];
                $qty = (int)$item['qty'];
                $isBundle = isset($item['isBundle']) && $item['isBundle'];
                
                if ($isBundle || (is_string($itemId) && strpos($itemId, 'bundle_') === 0)) {
                    // Handle Bundle
                    $bundleId = is_string($itemId) ? (int) str_replace('bundle_', '', $itemId) : $itemId;
                    
                    if (!$bundlesDb->has($bundleId)) {
                        DB::rollBack();
                        return back()->with('error', 'Bundle tidak valid ditemukan di keranjang.');
                    }
                    
                    $bundle = $bundlesDb[$bundleId];
                    if ($bundle->stok < $qty) {
                        DB::rollBack();
                        return back()->with('error', 'Stok bundle ' . $bundle->name . ' tidak mencukupi.');
                    }
                    
                    // IMPORTANT: Validate stock for ALL individual products in bundle first (WITH LOCK)
                    foreach ($bundle->items as $bundleItem) {
                        $requiredQty = $bundleItem->quantity * $qty;
                        $product = Product::where('product_id', $bundleItem->product_id)->lockForUpdate()->first();
                        
                        if (!$product || $product->stok < $requiredQty) {
                            DB::rollBack();
                            return back()->with('error', 'Stok produk "' . ($product->nama_produk ?? 'Unknown') . '" dalam bundle tidak mencukupi. Tersedia: ' . ($product->stok ?? 0) . ', Dibutuhkan: ' . $requiredQty);
                        }
                    }
                    
                    $totalHargaProduk += $bundle->price * $qty;
                    
                    // Calculate proportional subtotal for each item in bundle
                    // Bundle price is distributed across items based on their original prices
                    $bundleItemsCount = $bundle->items->count();
                    $bundleTotalPrice = $bundle->price * $qty;
                    
                    // Calculate total original price of all items in bundle (for proportional distribution)
                    $originalItemsTotal = 0;
                    foreach ($bundle->items as $bundleItem) {
                        $originalItemsTotal += ($bundleItem->product->harga ?? 0) * $bundleItem->quantity;
                    }
                    
                    // Create order items for each product in bundle
                    foreach ($bundle->items as $bundleItem) {
                        // Calculate proportional subtotal
                        if ($originalItemsTotal > 0) {
                            $itemOriginalPrice = ($bundleItem->product->harga ?? 0) * $bundleItem->quantity;
                            $itemSubtotal = ($itemOriginalPrice / $originalItemsTotal) * $bundleTotalPrice;
                        } else {
                            // If all items are free, distribute evenly
                            $itemSubtotal = $bundleItemsCount > 0 ? ($bundleTotalPrice / $bundleItemsCount) : 0;
                        }
                        
                        $orderItemsData[] = [
                            'product_id' => $bundleItem->product_id,
                            'kuantitas' => $bundleItem->quantity * $qty,
                            'subtotal' => round($itemSubtotal), // Distribute bundle price proportionally
                            'nik_data' => $request->input('nik') ? json_encode(['buyer_nik' => $request->input('nik')]) : null,
                            'bundle_id' => $bundleId,
                        ];

                        
                        // Decrement stock for individual products
                        Product::where('product_id', $bundleItem->product_id)->decrement('stok', $bundleItem->quantity * $qty);
                    }
                    
                    // Decrement bundle stock
                    Bundle::where('id', $bundleId)->decrement('stok', $qty);
                    
                } else {
                    // Handle Regular Product
                    if (!$productsDb->has($itemId)) {
                        DB::rollBack();
                        return back()->with('error', 'Produk tidak valid ditemukan di keranjang.');
                    }

                    $product = $productsDb[$itemId];
                    if ($product->stok < $qty) {
                        DB::rollBack();
                        return back()->with('error', 'Stok produk ' . $product->nama_produk . ' tidak mencukupi.');
                    }
                    
                    // VALIDASI ULANG KURSI saat checkout (mencegah double booking)
                    $seatIds = $item['seat_ids'] ?? [];
                    if (!empty($seatIds)) {
                        $seats = \App\Models\Seat::whereIn('id', $seatIds)->lockForUpdate()->get();
                        foreach ($seats as $seat) {
                            if ($seat->isBooked()) {
                                DB::rollBack();
                                $errorMsg = 'Maaf, kursi ' . $seat->seat_number . ' sudah dipesan oleh orang lain. Silakan pilih kursi lain.';
                                if ($request->wantsJson()) {
                                    return response()->json(['success' => false, 'message' => $errorMsg], 409);
                                }
                                return back()->with('error', $errorMsg);
                            }
                        }
                    }
                    
                    $totalHargaProduk += $product->harga * $qty;
                    
                    $orderItemsData[] = [
                        'product_id' => $itemId,
                        'kuantitas' => $qty,
                        'subtotal' => $product->harga * $qty,
                        'nik_data' => $request->input('nik') ? json_encode(['buyer_nik' => $request->input('nik')]) : null,
                        'seat_ids' => $seatIds,
                    ];
                }
            }
            
            // Calculate Fees
            $adminFee = 0;
            $serviceFee = 0;
            $ppn = 0;

            if ($applyTax) {
                // Admin Fee: Prosentase (default 3%) dengan Min (default 2000)
                $adminFeePct = (float) \App\Models\Setting::get('tax_admin_fee_percentage', 3);
                $adminFeeMin = (float) \App\Models\Setting::get('tax_admin_fee_min', 2000);
                
                $calculatedAdminFee = ($adminFeePct / 100) * $totalHargaProduk;
                $adminFee = max($calculatedAdminFee, $adminFeeMin);
                
                // QRIS is the only payment method
                $pct = (float) \App\Models\Setting::get('tax_service_fee_qris', 0.7);
                $serviceFee = ($pct / 100) * $totalHargaProduk;

                $ppnPct = (float) \App\Models\Setting::get('tax_ppn_percentage', 11);
                $ppn = ($ppnPct / 100) * ($adminFee + $serviceFee); 
            }

            // Discount
            $discountAmount = 0;
            $discount = null;
            if ($request->diskon_code) {
                $discount = Discount::where('code', $request->diskon_code)
                                    ->where('is_active', true)
                                    ->first(); // Add validations
                                    
                if ($discount) {
                    $discountAmount = ($discount->percentage / 100) * $totalHargaProduk; // Discount usually on product price
                }
            }

            $finalTotal = ($totalHargaProduk - $discountAmount) + $adminFee + $serviceFee + $ppn;
            
            // Cek apakah ini FREE order (total harga produk = 0)
            $isFreeOrder = $totalHargaProduk == 0;
            
            if ($isFreeOrder) {
                // Free order: set total to 0, status langsung Paid
                $finalTotal = 0;
            } else {
                $finalTotal = max(1, $finalTotal); // Ensure positive for paid orders
            }

            // Collect custom form data (fields starting with custom_field_)
            $customFieldData = [];
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'custom_field_') === 0) {
                    $customFieldData[$key] = $value;
                }
            }
            
            // Create Order
            $order = Order::create([
                'customer_id' => $customer->id,
                'total_harga' => $finalTotal, 
                'status' => $isFreeOrder ? 'Paid' : 'Pending', // Auto-confirm jika gratis
                'metode_pembayaran' => $isFreeOrder ? 'FREE' : strtoupper($request->metode_pembayaran), 
                'diskon_code' => optional($discount)->code,
                'diskon_amount' => $discountAmount,
                'fee_admin' => $adminFee,
                'fee_service' => $serviceFee,
                'fee_tax' => $ppn,
                'guest_token' => $request->input('guest_token'),
                'buyer_nik' => $request->input('nik'),
                'buyer_dob' => $request->input('dob'),
                'buyer_gender' => $request->input('gender'),
                'buyer_custom_data' => !empty($customFieldData) ? $customFieldData : null,
            ]);

            foreach ($orderItemsData as $item) {
                // Remove seat_ids before passing to create to avoid SQL errors
                $seatIds = $item['seat_ids'] ?? [];
                unset($item['seat_ids']);
                
                $orderItem = $order->orderItems()->create($item);
                
                // Attach seats
                if (!empty($seatIds)) {
                    $orderItem->seats()->attach($seatIds);
                }
                
                // Only decrement stock for regular products (not bundled items - they were already decremented)
                if (!isset($item['bundle_id'])) {
                    Product::where('product_id', $item['product_id'])->decrement('stok', $item['kuantitas']);
                }
            }

            if ($discount) $discount->increment('used_count');

            // Jika FREE order, kirim email konfirmasi langsung & redirect ke invoice
            if ($isFreeOrder) {
                // Generate individual tickets for digital items
                $this->generateTicketsForOrder($order);
                
                try {
                    Mail::to($customer->email)->send(new OrderConfirmation($order));
                } catch (\Exception $e) {
                    Log::error('Gagal kirim email free order #'.$order->order_id.': '.$e->getMessage());
                }
                
                // Clear Cart Session
                \Illuminate\Support\Facades\Session::forget('cart_items');

                DB::commit();
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'is_free' => true,
                        'order_id' => $order->order_id,
                        'message' => 'Pendaftaran berhasil! E-tiket akan dikirim ke email Anda.',
                    ]);
                }
                
                return redirect()->route('public.invoice', $order->order_id)
                    ->with('success', 'Pendaftaran berhasil! E-tiket telah dikirim ke email Anda.');
            }

            // Get payment method from request
            $paymentMethod = $request->metode_pembayaran ?? 'qris';
            
            $midtransService = new MidtransService();
            $enabledPayments = [];
            
            // Logic untuk menentukan enabled_payments berdasarkan pilihan user
            switch ($paymentMethod) {
                case 'bank_transfer':
                    // Include all VA types + echannel (Mandiri Bill Payment)
                    $enabledPayments = [
                        'bca_va', 'bni_va', 'bri_va', 'permata_va',
                        'cimb_va', 'bsi_va', 'danamon_va',
                        'echannel',  // Mandiri Bill Payment
                        'other_va'   // Other banks
                    ];
                    break;
                case 'gopay':
                    $enabledPayments = ['gopay', 'shopeepay'];
                    break;
                case 'qris':
                    $enabledPayments = ['other_qris'];
                    break;
                default:
                    // Fallback default (all)
                    $enabledPayments = [
                        'bca_va', 'bni_va', 'bri_va', 'permata_va',
                        'cimb_va', 'bsi_va', 'danamon_va',
                        'echannel', 'other_va',
                        'gopay', 'shopeepay',
                        'other_qris'
                    ];
                    break;
            }

            // Jika pilih QRIS, coba gunakan Core API langsung
            if ($paymentMethod === 'qris') {
                try {
                    $qrisResult = $midtransService->createQrisTransaction($order, $customer);
                    
                    if ($qrisResult['success']) {
                        // Simpan QR data ke order untuk ditampilkan di invoice
                        $order->midtrans_transaction_id = $qrisResult['transaction_id'];
                        $order->qris_url = $qrisResult['qr_url'];
                        $order->qris_string = $qrisResult['qr_string'];
                        $order->save();
                        
                        // Clear Cart Session
                        \Illuminate\Support\Facades\Session::forget('cart_items');

                        DB::commit();
                        
                        if ($request->wantsJson()) {
                            return response()->json([
                                'success' => true,
                                'is_free' => false,
                                'payment_type' => 'qris',
                                'qr_url' => $qrisResult['qr_url'],
                                'order_id' => $order->order_id,
                            ]);
                        }
                        
                        return redirect()->route('public.invoice', $order->order_id);
                    } else {
                        throw new \Exception($qrisResult['error'] ?? 'QRIS transaction failed');
                    }
                } catch (\Exception $e) {
                    Log::error('QRIS Core API Error: ' . $e->getMessage() . '. Fallback to Snap.');
                    // Fallback ke Snap dengan enabledPayments yang sudah diset ['other_qris']
                }
            }
            
            // Gunakan Snap (Untuk VA, E-Wallet, atau Fallback QRIS)
            try {
                $snapToken = $midtransService->createSnapTransaction($order, $customer, $enabledPayments);
                $order->midtrans_snap_token = $snapToken;
                $order->save();
            } catch (\Exception $e) {
                 Log::error('Midtrans Snap Error: ' . $e->getMessage());
            }

            // Clear Cart Session
            \Illuminate\Support\Facades\Session::forget('cart_items');

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'is_free' => false,
                    'payment_type' => 'snap',
                    'snap_token' => $order->midtrans_snap_token,
                    'order_id' => $order->order_id,
                ]);
            }

             return redirect()->route('public.invoice', $order->order_id); // Fallback

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage());
            if ($request->wantsJson()) {
                 return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }
    
    public function showPaymentInstructions($order_id)
    {
        $order = Order::with('customer')->findOrFail($order_id);
        
        if ($order->status !== 'Pending') {
            return redirect()->route('public.invoice', $order->order_id)
                             ->with('error', 'Pesanan ini sudah diproses.');
        }

        // Fetch Manual Payment Settings
        $settings = \App\Models\Setting::whereIn('key', ['qris_path', 'admin_whatsapp'])->pluck('value', 'key');
        $qrisPath = $settings['qris_path'] ?? null;
        $adminWhatsapp = $settings['admin_whatsapp'] ?? null;

        return view('public.payment_instructions', compact('order', 'qrisPath', 'adminWhatsapp'));
    }

    public function simulatePaymentConfirmation($order_id)
    {
        $order = Order::with(['customer', 'orderItems.product'])->findOrFail($order_id);
        
        $message = 'Pembayaran berhasil disimulasikan.';

        if ($order->status === 'Pending') {
            $order->status = 'Paid';
            $order->save();

            $hasDigital = $order->orderItems->contains(function($item) {
                return $item->product->tipe === 'Digital';
            });

            if ($hasDigital) {
                $message .= ' Tiket & Invoice telah dikirim ke email Anda.';
            } else {
                $message .= ' Invoice & Info Pengiriman telah dikirim ke email Anda.';
            }

            if ($order->customer && $order->customer->email) {
                try {
                    Mail::to($order->customer->email)->send(new OrderConfirmation($order));
                } catch (\Exception $e) {
                    Log::error('Gagal kirim email #'.$order->order_id.': '.$e->getMessage());
                    $message .= ' (Gagal mengirim email konfirmasi, silakan cek folder Spam atau hubungi admin)';
                }
            }
        }

        return redirect()->route('public.invoice', $order->order_id)
                         ->with('success', $message);
    }

    public function handlePaymentNotification(Request $request)
    {
        try {
            $payload = $request->all();
            
            Log::info('Midtrans Notification Raw:', $payload);

            $orderId = $payload['order_id'];
            $statusCode = $payload['status_code'];
            $grossAmount = $payload['gross_amount'];
            $serverKey = config('services.midtrans.server_key');

            if (empty($serverKey)) {
                return response()->json(['message' => 'Server Key not configured'], 500);
            }
            
            // Handle Midtrans Test Payload
            if (strpos($orderId, 'test') !== false) {
                return response()->json(['message' => 'Test notification received']);
            }

            $input = $orderId . $statusCode . $grossAmount . $serverKey;
            $signature = hash('sha512', $input);
            
            if ($signature !== $payload['signature_key']) {
                Log::warning('Invalid Signature for Order: ' . $orderId);
                return response()->json(['message' => 'Invalid Signature'], 403);
            }

            $transactionStatus = $payload['transaction_status'];
            
            // Search Order Safely
            $order = Order::where('order_id', $orderId)->first();
            if (!$order) {
                 // Fallback check by ID if order_id column usage is mixed
                 // Use binding to prevent SQL injection or Type Errors
                 $order = Order::where('id', $orderId)->first();
            }

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                if ($order->status !== 'Paid') {
                    $order->status = 'Paid';
                    $order->payment_status = 'Paid';
                    
                    // Update Metode Pembayaran dari Callback (misal: 'qris', 'gopay')
                    if (isset($payload['payment_type'])) {
                        $order->metode_pembayaran = $payload['payment_type'];
                    }
                    
                    $order->save();
                    
                    // Generate individual tickets for digital items
                    $this->generateTicketsForOrder($order);
                    
                    // Email ONLY Sent on Paid
                    if ($order->customer && $order->customer->email) {
                        try {
                            Mail::to($order->customer->email)->send(new OrderConfirmation($order));
                        } catch (\Exception $e) {
                            Log::error('Failed sending email: ' . $e->getMessage());
                        }
                    }
                }
            } elseif ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                if ($order->status !== 'Cancelled') {
                    $order->status = 'Cancelled'; // Map expire/deny to Cancelled
                    $order->payment_status = 'Failed';
                    $order->save();

                    // Restore Stock if Cancelled
                    foreach ($order->orderItems as $item) {
                         if ($item->product) {
                             $item->product->increment('stok', $item->kuantitas);
                         }
                    }
                    
                    // Restore Discount Use
                    if ($order->diskon_code) {
                        $discount = Discount::where('code', $order->diskon_code)->first();
                        if ($discount) $discount->decrement('used_count');
                    }
                }
            } else if ($transactionStatus == 'pending') {
                $order->status = 'Pending';
                $order->save();
            }

            return response()->json(['message' => 'Notification processed']);

        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function showInvoice($order_id)
    {
        $order = Order::with(['orderItems.product', 'customer'])
                      ->findOrFail($order_id);

        // PROTEKSI: Hanya pemilik order yang boleh melihat invoice
        $customer = Auth::guard('customer')->user();
        if (!$customer || $customer->id !== $order->customer_id) {
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }

        $adminWhatsapp = \App\Models\Setting::where('key', 'admin_whatsapp')->value('value');

        $snapToken = null;
        if ($order->status === 'Pending' && $order->midtrans_snap_token) {
            $snapToken = $order->midtrans_snap_token;
        }

        return view('public.invoice', compact('order', 'adminWhatsapp', 'snapToken'));
    }

    public function guestHistory(Request $request)
    {
        $guestToken = $request->input('guest_token');

        if (!$guestToken) {
            return redirect()->route('public.index')->with('error', 'Guest Session not found.');
        }

        $orders = Order::with(['orderItems.product.event']) 
                        ->where('guest_token', $guestToken)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('public.history', compact('orders'));
    }

    /**
     * Generate individual tickets for all digital items in an order.
     * Each ticket gets a unique code like TIKET-{item_id}-{sequence}
     */
    protected function generateTicketsForOrder(Order $order)
    {
        $order->load('orderItems.product');
        
        foreach ($order->orderItems as $orderItem) {
            // Only generate tickets for digital products and seminars
            if (!$orderItem->product || !in_array($orderItem->product->tipe, ['Digital', 'Seminar'])) {
                continue;
            }
            
            // Check if tickets already exist for this order item
            if ($orderItem->tickets()->count() > 0) {
                continue;
            }
            
            // Create individual tickets based on quantity
            \App\Models\Ticket::createForOrderItem($orderItem);
        }
    }

    // =========================================================================
    // WAITING ROOM (Virtual Queue)
    // =========================================================================

    /**
     * Tampilkan halaman ruang tunggu virtual.
     */
    public function showWaitingRoom(Request $request, $event_id)
    {
        $event = Event::findOrFail($event_id);
        $sessionId = $request->session()->getId();

        // Cleanup & promote
        WaitingRoomEntry::cleanup($event_id);
        WaitingRoomEntry::promoteNext($event_id);

        $entry = WaitingRoomEntry::findOrCreateForSession($sessionId, $event_id);

        // Jika sudah aktif, redirect langsung ke event
        if ($entry->status === 'active') {
            return redirect()->route('public.event.detail', $event_id);
        }

        return view('public.waiting_room', compact('event', 'entry'));
    }

    /**
     * API: Cek status antrian (dipanggil via polling dari halaman waiting room).
     */
    public function checkWaitingStatus(Request $request, $event_id)
    {
        $token = $request->query('token');

        if (!$token) {
            return response()->json(['status' => 'expired', 'message' => 'Token tidak valid'], 400);
        }

        // Cleanup & promote
        WaitingRoomEntry::cleanup($event_id);
        WaitingRoomEntry::promoteNext($event_id);

        $entry = WaitingRoomEntry::where('token', $token)
                    ->where('event_id', $event_id)
                    ->first();

        if (!$entry) {
            return response()->json(['status' => 'expired', 'message' => 'Sesi antrian tidak ditemukan']);
        }

        // Refresh entry setelah promote
        $entry->refresh();

        return response()->json([
            'status' => $entry->status,
            'position' => $entry->position,
            'estimated_wait' => $entry->estimated_wait,
            'active_users' => WaitingRoomEntry::activeCount($event_id),
            'max_active' => WaitingRoomEntry::MAX_ACTIVE_USERS,
        ]);
    }

}