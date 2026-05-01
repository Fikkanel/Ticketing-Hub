<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail; 
use App\Mail\OrderConfirmation;      
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;



class AdminController extends Controller
{

    public function manageBookings(Request $request)
    {
        // Use Ticket model for individual ticket tracking
        $query = \App\Models\Ticket::with([
            'orderItem.order.customer', 
            'orderItem.product.event', 
            'scannedBy'
        ])
        ->whereHas('orderItem.product', function($q) {
            $q->whereIn('tipe', ['Digital', 'Seminar']);
        })
        ->whereHas('orderItem.order', function($q) {
            $q->where('status', 'Paid');
        })
        ->orderBy('created_at', 'desc');

        // 0. Siapkan List Event untuk Dropdown Filter
        $user = Auth::user();
        $eventsQuery = Event::orderBy('tgl_mulai', 'desc');
        
        if (!$user->isSuperAdmin()) {
            $assignedEventIds = $user->events->pluck('event_id');
            $eventsQuery->whereIn('event_id', $assignedEventIds);
        }
        $availableEvents = $eventsQuery->pluck('judul', 'event_id');

        // 1. Filter by Assigned Events (Role Restriction - Base Query)
        if (!$user->isSuperAdmin()) {
            $query->whereHas('orderItem.product', function($q) use ($assignedEventIds) {
                $q->whereIn('event_id', $assignedEventIds);
            });
        }
        
        // 2. Filter Spesifik dari Dropdown (Request User)
        $selectedEventId = $request->input('event_id');
        if ($selectedEventId) {
            $query->whereHas('orderItem.product', function($q) use ($selectedEventId) {
                $q->where('event_id', $selectedEventId);
            });
        }

        // 3. Logika "Awalnya Harus Pilih Dulu"
        if (!$selectedEventId && !$request->filled('q') && !$request->filled('status')) {
             $query->where('id', -1); // Return empty
        }

        // Text Search
        if ($request->has('q') && !empty($request->q)) {
             $search = $request->q;
             $query->where(function($q) use ($search) {
                 $q->where('ticket_code', 'like', "%{$search}%")
                   ->orWhereHas('orderItem', function($subQ) use ($search) {
                       $subQ->where('item_id', 'like', "%{$search}%");
                   })
                   ->orWhereHas('orderItem.order', function($subQ) use ($search) {
                       $subQ->where('order_id', 'like', "%{$search}%")
                            ->orWhereHas('customer', function($custQ) use ($search) {
                                $custQ->where('name', 'like', "%{$search}%");
                            });
                   });
             });
        }
        
        // Filter by Status Scan
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status == 'scanned') {
                $query->where('is_scanned', true);
            } else {
                $query->where('is_scanned', false);
            }
        }

        $bookings = $query->paginate(20);
        return view('admin.bookings.index', compact('bookings', 'availableEvents', 'selectedEventId'));
    }

    public function emptyBookings()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            $user = Auth::user();
            
            if ($user->isSuperAdmin()) {
                // Superadmin: Hapus SEMUA
                OrderItem::truncate();
                Order::truncate();
            } else {
                // Admin: Hapus berdasarkan Event yang dipegang
                $assignedEventIds = $user->events->pluck('event_id');
                
                // 1. Cari OrderItem dari event tersebut
                $items = OrderItem::whereHas('product', function($q) use ($assignedEventIds) {
                    $q->whereIn('event_id', $assignedEventIds);
                })->get();

                $orderIdsToCheck = $items->pluck('order_id')->unique();

                foreach ($items as $item) {
                    $item->delete();
                }

                // 2. Cek Order (Hapus jika kosong)
                foreach ($orderIdsToCheck as $orderId) {
                    $order = Order::find($orderId);
                    if ($order && $order->orderItems()->count() == 0) {
                        $order->delete();
                    }
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('success', 'Data Booking berhasil dikosongkan.');

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
    // =========================================================================
    // 1. AUTENTIKASI
    // =========================================================================

    public function showLoginForm()
    {
        // If already logged in, redirect to dashboard (prevent back to login)
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Hubungi Superadmin.',
                ])->onlyInput('email');
            }

            if (Auth::user()->isScanner()) {
                return redirect()->route('admin.bookings.index');
            }

            return redirect()->intended(route('admin.dashboard')); 
        }

        return back()->withErrors([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function showChangePasswordForm()
    {
        return view('admin.change_password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.']);
        }

        Auth::user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password berhasil diperbarui!');
    }

    // =========================================================================
    // 2. DASHBOARD & STATISTIK
    // =========================================================================

    public function dashboard(Request $request)
    {
        $user = Auth::user();

        // Redirect Scanner to Bookings Page
        if ($user->isScanner()) {
            return redirect()->route('admin.bookings.index');
        }

        // Siapkan daftar event milik user (untuk dropdown switcher)
        $myEvents = $user->isSuperAdmin() ? Event::all() : $user->events;
        
        // Tentukan Scope Event ID
        $selectedEventId = $request->query('event_id');
        
        // Validasi akses ke event ID yang dipilih
        if ($selectedEventId && !$user->isSuperAdmin() && !$user->events->contains('event_id', $selectedEventId)) {
            $selectedEventId = null; // Reset jika tidak punya akses
        }

        // Logic Statistik
        $queryOrders = Order::query();
        $queryRevenue = Order::whereIn('status', ['Paid', 'Shipped']);
        
        if ($selectedEventId) {
            // Filter Spesifik Satu Event
            $queryOrders->whereHas('orderItems.product', function($q) use ($selectedEventId) {
                $q->where('event_id', $selectedEventId);
            });
            
            $queryRevenue->whereHas('orderItems.product', function($q) use ($selectedEventId) {
                $q->where('event_id', $selectedEventId);
            });
            // Revenue calculation logic is complex for mixed orders, for now assumed filters work on order level roughly
            // but for exactness we sum items.
        } elseif (!$user->isSuperAdmin()) {
            // Filter Agregat (Semua Event milik Admin)
            $myEventIds = $myEvents->pluck('event_id');
            $queryOrders->whereHas('orderItems.product', function($q) use ($myEventIds) {
                $q->whereIn('event_id', $myEventIds);
            });
             $queryRevenue->whereHas('orderItems.product', function($q) use ($myEventIds) {
                $q->whereIn('event_id', $myEventIds);
            });
        }

        $totalOrders = $queryOrders->count();
        
        // Revenue Calculation (More precise to sum items)
        // If specific event is selected OR admin constrained, we should sum only relevant items
        if ($selectedEventId || !$user->isSuperAdmin()) {
             $targetEventIds = $selectedEventId ? [$selectedEventId] : $myEvents->pluck('event_id')->toArray();
             
             // Get orders that contain items from target events
             $orders = $queryRevenue->with(['orderItems.product'])->get();
             $totalRevenue = $orders->sum(function($order) use ($targetEventIds) {
                 return $order->orderItems->filter(function($item) use ($targetEventIds) {
                     if (!$item->product) return false;
                     return in_array($item->product->event_id, $targetEventIds);
                 })->sum('subtotal'); // Use 'subtotal' field which exists in order_items table
             });
        } else {
             // Sum only ticket prices: total_harga minus all fees (admin, service, tax)
             $totalRevenue = $queryRevenue->get()->sum(function($order) {
                 return $order->total_harga - ($order->fee_admin ?? 0) - ($order->fee_service ?? 0) - ($order->fee_tax ?? 0);
             });
        }

        // Admin Fee & Platform Fee Totals
        $feeQuery = Order::whereIn('status', ['Paid', 'Shipped']);
        if ($selectedEventId) {
            $feeQuery->whereHas('orderItems.product', function($q) use ($selectedEventId) {
                $q->where('event_id', $selectedEventId);
            });
        } elseif (!$user->isSuperAdmin()) {
            $myEventIds = $myEvents->pluck('event_id');
            $feeQuery->whereHas('orderItems.product', function($q) use ($myEventIds) {
                $q->whereIn('event_id', $myEventIds);
            });
        }
        $totalAdminFee = (clone $feeQuery)->sum('fee_admin');
        $totalPlatformFee = (clone $feeQuery)->sum('fee_service');

        // Active Events Count
        if ($selectedEventId) {
            $activeEvents = 1;
        } elseif (!$user->isSuperAdmin()) {
            $activeEvents = $myEvents->where('status', 'Active')->count();
        } else {
            $activeEvents = Event::where('status', 'Active')->count();
        }

        // Best Selling
        $bestSellingQuery = OrderItem::select('product_id', DB::raw('SUM(kuantitas) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product');

        if ($selectedEventId) {
             $bestSellingQuery->whereHas('product', function($q) use ($selectedEventId) {
                $q->where('event_id', $selectedEventId);
            });
        } elseif (!$user->isSuperAdmin()) {
             $myEventIds = $myEvents->pluck('event_id');
             $bestSellingQuery->whereHas('product', function($q) use ($myEventIds) {
                $q->whereIn('event_id', $myEventIds);
            });
        }

        $bestSelling = $bestSellingQuery->first();

        $bestSellingProduct = ($bestSelling && $bestSelling->product) 
            ? $bestSelling->product->nama_produk 
            : '-';
            
        // --- ANALYTICS DATA PREPARATION ---
        
        // 1. REVENUE TREND (Last 30 Days)
        // Clone queryRevenue which already has correct filtering (Status Paid/Shipped + Event Restrictions)
        $revenueTrend = (clone $queryRevenue)
            ->selectRaw('DATE(created_at) as date, SUM(total_harga - COALESCE(fee_admin, 0) - COALESCE(fee_service, 0) - COALESCE(fee_tax, 0)) as daily_total')
            ->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $chartDates = [];
        $chartRevenue = [];
        
        // Fill missing dates with 0
        $period = \Carbon\CarbonPeriod::create(\Carbon\Carbon::now()->subDays(29), \Carbon\Carbon::now());
        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $chartDates[] = $date->format('d M');
            $dayData = $revenueTrend->firstWhere('date', $dateString);
            $chartRevenue[] = $dayData ? $dayData->daily_total : 0;
        }

        // 2. TOP 5 SELLING PRODUCTS
        // Clone bestSellingQuery for consistent event filtering
        $topProductsData = (clone $bestSellingQuery)
            ->take(5)
            ->get();
            
        $topProductLabels = $topProductsData->map(fn($item) => $item->product->nama_produk ?? 'Unknown')->toArray();
        $topProductQty = $topProductsData->pluck('total_sold')->toArray();

        // 3. REAL-TIME CHECK-IN STATS
        // Check-in logic: Count OrderItems (Tickets) that are scanned vs total valid tickets
        $ticketQuery = OrderItem::with('product')
            ->whereHas('order', function($q) {
                $q->where('status', 'Paid'); // Confirmed orders only
            })
            ->whereHas('product', function($q) {
                $q->whereIn('tipe', ['Digital', 'Seminar']); // Digital and Seminar items have check-in
            });

        // Apply Event Filter to Ticket Query
        if ($selectedEventId) {
            $ticketQuery->whereHas('product', function($q) use ($selectedEventId) {
                $q->where('event_id', $selectedEventId);
            });
        } elseif (!$user->isSuperAdmin()) {
            $myEventIds = $myEvents->pluck('event_id');
            $ticketQuery->whereHas('product', function($q) use ($myEventIds) {
                $q->whereIn('event_id', $myEventIds);
            });
        }

        $totalTickets = $ticketQuery->count();
        $checkedInCount = (clone $ticketQuery)->where('is_scanned', true)->count();
        $checkInPercentage = $totalTickets > 0 ? round(($checkedInCount / $totalTickets) * 100, 1) : 0;
        
        $currentEvent = $selectedEventId ? Event::find($selectedEventId) : null;

        return view('admin.dashboard', compact(
            'totalOrders', 'totalRevenue', 'activeEvents', 'bestSellingProduct', 
            'totalAdminFee', 'totalPlatformFee',
            'myEvents', 'selectedEventId', 'currentEvent',
            'chartDates', 'chartRevenue', 
            'topProductLabels', 'topProductQty',
            'totalTickets', 'checkedInCount', 'checkInPercentage'
        ));
    }

    // =========================================================================
    // 3. MANAJEMEN EVENT
    // =========================================================================

    public function manageEvents()
    {
        $query = Event::with('location')->orderBy('created_at', 'desc');
        
        if (!Auth::user()->isSuperAdmin()) {
            // Filter by related events
            $eventIds = Auth::user()->events->pluck('event_id');
            $query->whereIn('event_id', $eventIds);
        }

        $events = $query->get();
        return view('admin.manage_events', compact('events'));
    }

    public function createEvent()
    {
        if (!Auth::user()->isSuperAdmin()) abort(403, 'Hanya Superadmin yang dapat membuat event.');
        $locations = Location::pluck('nama_lokasi', 'location_id');
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('admin.create_event', compact('locations', 'categories'));
    }

    public function storeEvent(Request $request)
    {
        if (!Auth::user()->isSuperAdmin()) abort(403, 'Hanya Superadmin yang dapat membuat event.');

        $request->validate([
            'judul' => 'required|max:200',
            'location_id' => 'required|exists:locations,location_id',
            'tgl_mulai' => 'required|date',
            'status' => ['required', Rule::in(['Upcoming', 'Active', 'Finished'])],
            'ticket_type' => 'nullable|string|max:50',
            'custom_email_content' => 'nullable|string',
            'banner_file' => 'nullable|image|max:2048',
            'card_file'   => 'nullable|image|max:2048',
            'payment_channels' => ['required', Rule::in(['all', 'qris_only'])],
            'payment_mode' => ['required', Rule::in(['regular', 'sponsorship'])],
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'layout_type' => ['nullable', Rule::in(['default', 'manual_map'])],
        ]);

        $data = $request->except(['banner_file', 'banner_url', 'card_file', 'card_url', '_token', 'categories']);
        $data['banner_image'] = $this->handleImageUpload($request, 'banner_file', 'banner_url', 'banners');
        $data['card_image'] = $this->handleImageUpload($request, 'card_file', 'card_url', 'cards');
        
        // Fix for ticket_type constraint
        if (empty($data['ticket_type'])) {
            $data['ticket_type'] = 'E-TICKET';
        }

        $event = Event::create($data);
        
        // Sync categories (many-to-many)
        if ($request->has('categories')) {
            $event->categories()->sync($request->categories);
        }

        // Save Lineups
        if ($request->has('lineups') && is_array($request->lineups)) {
            foreach ($request->lineups as $index => $lineupData) {
                if (!empty($lineupData['name'])) {
                    $imagePath = null;
                    if ($request->hasFile("lineups.{$index}.image_file")) {
                        $imagePath = $request->file("lineups.{$index}.image_file")->store('events/lineups', 'public');
                    }

                    \App\Models\EventLineup::create([
                        'event_id' => $event->event_id,
                        'name' => $lineupData['name'],
                        'instagram_url' => $lineupData['instagram_url'] ?? null,
                        'image_path' => $imagePath,
                    ]);
                }
            }
        }

        // Save Facilities
        if ($request->has('facilities') && is_array($request->facilities)) {
            foreach ($request->facilities as $facilityData) {
                if (!empty($facilityData['name'])) {
                    \App\Models\EventFacility::create([
                        'event_id' => $event->event_id,
                        'name' => $facilityData['name'],
                        'image_path' => $facilityData['image_path'] ?? 'fas fa-check-circle',
                    ]);
                }
            }
        }

        return redirect()->route('admin.events')->with('success', 'Event baru berhasil ditambahkan!');
    }

    public function editEvent($event_id)
    {
        $event = Event::findOrFail($event_id);
        
        // Security Check
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->events->contains('event_id', $event_id)) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }

        $locations = Location::pluck('nama_lokasi', 'location_id');
        $categories = \App\Models\Category::orderBy('name')->get();
        return view('admin.edit_event', compact('event', 'locations', 'categories'));
    }

    public function updateEvent(Request $request, $event_id)
    {
        $event = Event::findOrFail($event_id);

        if (!Auth::user()->isSuperAdmin() && !Auth::user()->events->contains('event_id', $event_id)) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }
        $request->validate([
            'judul' => 'required|max:200',
            'location_id' => 'required|exists:locations,location_id',
            'status' => ['required', Rule::in(['Upcoming', 'Active', 'Finished'])],
            'ticket_type' => 'nullable|string|max:50',
            'custom_email_content' => 'nullable|string',
            // Payment settings are only editable by superadmin, so make them nullable
            'payment_channels' => ['nullable', Rule::in(['all', 'qris_only'])],
            'payment_mode' => ['nullable', Rule::in(['regular', 'sponsorship'])],
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'layout_type' => ['nullable', Rule::in(['default', 'manual_map'])],
        ]);

        // Exclude payment settings from data if not present (non-superadmin users)
        $excludeFields = ['banner_file', 'banner_url', 'card_file', 'card_url', '_token', '_method', 'categories'];
        if (!$request->filled('payment_channels')) {
            $excludeFields[] = 'payment_channels';
        }
        if (!$request->filled('payment_mode')) {
            $excludeFields[] = 'payment_mode';
        }
        $data = $request->except($excludeFields);

        $newBanner = $this->handleImageUpload($request, 'banner_file', 'banner_url', 'banners');
        if ($newBanner) {
            if ($event->banner_image && !str_starts_with($event->banner_image, 'http')) {
                Storage::disk('public')->delete($event->banner_image);
            }
            $data['banner_image'] = $newBanner;
        }

        $newCard = $this->handleImageUpload($request, 'card_file', 'card_url', 'cards');
        if ($newCard) {
            if ($event->card_image && !str_starts_with($event->card_image, 'http')) {
                Storage::disk('public')->delete($event->card_image);
            }
            $data['card_image'] = $newCard;
        }

        // Fix for ticket_type constraint
        if (array_key_exists('ticket_type', $data) && empty($data['ticket_type'])) {
            $data['ticket_type'] = 'E-TICKET';
        }

        // Process Unique Data Settings
        // Boolean fields (checkbox): set to 0 if not checked
        $data['limit_one_email_per_transaction'] = $request->has('limit_one_email_per_transaction') ? 1 : 0;
        $data['require_unique_data_per_ticket'] = $request->has('require_unique_data_per_ticket') ? 1 : 0;
        
        // buyer_form_fields: convert checkbox array to proper JSON structure
        if ($request->has('buyer_form_fields')) {
            $buyerFields = [];
            foreach ($request->input('buyer_form_fields', []) as $field) {
                $buyerFields[] = [
                    'field' => $field['field'] ?? '',
                    'enabled' => isset($field['enabled']) && $field['enabled'] == '1',
                    'required' => isset($field['required']) && $field['required'] == '1',
                ];
            }
            $data['buyer_form_fields'] = $buyerFields;
        }
        
        // custom_form_fields: convert to proper JSON structure
        if ($request->has('custom_form_fields')) {
            $customFields = [];
            foreach ($request->input('custom_form_fields', []) as $cf) {
                if (!empty($cf['label'])) {
                    $options = isset($cf['options']) && !empty($cf['options']) 
                        ? array_map('trim', explode(',', $cf['options'])) 
                        : [];
                    $customFields[] = [
                        'name' => \Illuminate\Support\Str::slug($cf['label'], '_'),
                        'label' => $cf['label'],
                        'type' => $cf['type'] ?? 'text',
                        'options' => $options,
                        'required' => isset($cf['required']) && $cf['required'] == '1',
                    ];
                }
            }
            $data['custom_form_fields'] = $customFields;
        }

        $event->update($data);
        
        // Sync categories (many-to-many)
        $event->categories()->sync($request->categories ?? []);
        
        // Sync Lineups
        if ($request->has('lineups') && is_array($request->lineups)) {
            $keptLineupIds = [];
            foreach ($request->lineups as $index => $lineupData) {
                if (!empty($lineupData['name'])) {
                    $lineup = null;
                    if (!empty($lineupData['id'])) {
                        $lineup = \App\Models\EventLineup::find($lineupData['id']);
                    }
                    
                    if (!$lineup) {
                        $lineup = new \App\Models\EventLineup();
                        $lineup->event_id = $event->event_id;
                    }

                    $lineup->name = $lineupData['name'];
                    $lineup->instagram_url = $lineupData['instagram_url'] ?? null;

                    if ($request->hasFile("lineups.{$index}.image_file")) {
                        if ($lineup->image_path) {
                            Storage::disk('public')->delete($lineup->image_path);
                        }
                        $lineup->image_path = $request->file("lineups.{$index}.image_file")->store('events/lineups', 'public');
                    }
                    
                    $lineup->save();
                    $keptLineupIds[] = $lineup->id;
                }
            }
            
            // Delete removed lineups
            $lineupsToDelete = \App\Models\EventLineup::where('event_id', $event->event_id)
                                ->whereNotIn('id', $keptLineupIds)->get();
            foreach ($lineupsToDelete as $toDelete) {
                if ($toDelete->image_path) {
                    Storage::disk('public')->delete($toDelete->image_path);
                }
                $toDelete->delete();
            }
        } else {
            // Delete all if empty
            $lineupsToDelete = \App\Models\EventLineup::where('event_id', $event->event_id)->get();
            foreach ($lineupsToDelete as $toDelete) {
                if ($toDelete->image_path) {
                    Storage::disk('public')->delete($toDelete->image_path);
                }
                $toDelete->delete();
            }
        }
        
        // Sync Facilities
        if ($request->has('facilities') && is_array($request->facilities)) {
            $keptFacilityIds = [];
            foreach ($request->facilities as $facilityData) {
                if (!empty($facilityData['name'])) {
                    $facility = null;
                    if (!empty($facilityData['id'])) {
                        $facility = \App\Models\EventFacility::find($facilityData['id']);
                    }
                    
                    if (!$facility) {
                        $facility = new \App\Models\EventFacility();
                        $facility->event_id = $event->event_id;
                    }

                    $facility->name = $facilityData['name'];
                    if (isset($facilityData['image_path'])) {
                        $facility->image_path = $facilityData['image_path'];
                    }

                    $facility->save();
                    $keptFacilityIds[] = $facility->id;
                }
            }
            
            // Delete removed facilities
            $facilitiesToDelete = \App\Models\EventFacility::where('event_id', $event->event_id)
                                ->whereNotIn('id', $keptFacilityIds)->get();
            foreach ($facilitiesToDelete as $toDelete) {
                $toDelete->delete();
            }
        } else {
            // Delete all if empty
            $facilitiesToDelete = \App\Models\EventFacility::where('event_id', $event->event_id)->get();
            foreach ($facilitiesToDelete as $toDelete) {
                $toDelete->delete();
            }
        }
        
        return redirect()->route('admin.events')->with('success', 'Event berhasil diperbarui!');
    }

    public function deleteEvent($event_id)
    {
        $event = Event::findOrFail($event_id);

        if (!Auth::user()->isSuperAdmin()) {
            // Strict: Maybe superadmin only? Or admin can delete their own event?
            // Let's assume only superadmin or admin with access can delete, but user asked to restrict "adding" event.
            // Safe bet: if admin has access, they can delete.
             if (!Auth::user()->events->contains('event_id', $event_id)) {
                abort(403, 'Anda tidak memiliki akses ke event ini.');
             }
        }
        try {
            if ($event->banner_image && !str_starts_with($event->banner_image, 'http')) {
                Storage::disk('public')->delete($event->banner_image);
            }
            $event->delete();
            return redirect()->route('admin.events')->with('success', 'Event berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('admin.events')->with('error', 'Gagal menghapus event.');
        }
    }

    private function handleImageUpload($request, $fileInputName, $urlInputName, $destinationFolder)
    {
        if ($request->hasFile($fileInputName)) {
            return $request->file($fileInputName)->store('events/' . $destinationFolder, 'public');
        }
        if ($request->filled($urlInputName)) {
            return $request->input($urlInputName);
        }
        return null;
    }

    // =========================================================================
    // 4. MANAJEMEN ORDER
    // =========================================================================

    public function manageOrders(Request $request)
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
             $events = Event::orderBy('tgl_mulai', 'desc')->pluck('judul', 'event_id');
        } else {
             $events = $user->events->pluck('judul', 'event_id');
        }
        
        $selectedEventId = $request->input('event_id');

        $query = Order::with(['customer', 'orderItems.product']);

        if ($selectedEventId) {
            $query->whereHas('orderItems.product', function($q) use ($selectedEventId) {
                $q->withTrashed()->where('products.event_id', $selectedEventId);
            });
        } elseif (!$user->isSuperAdmin()) {
             // If no specific event selected, show orders from ALL user's events
            $myEventIds = $user->events->pluck('event_id');
            $query->whereHas('orderItems.product', function($q) use ($myEventIds) {
                $q->withTrashed()->whereIn('products.event_id', $myEventIds);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%") 
                  ->orWhereHas('customer', function($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%") 
                        ->orWhere('email', 'like', "%{$search}%"); 
                  });
            });
        }

        $orders = $query->orderBy('tgl_order', 'desc')->paginate(10);

        if ($request->ajax()) {
            return view('admin.partials.order_rows', compact('orders'))->render();
        }
                       
        return view('admin.manage_orders', compact('orders', 'events', 'selectedEventId'));
    }

    public function updateOrderStatus(Request $request, $order_id)
    {
        $request->validate(['status' => ['required', Rule::in(['Pending', 'Paid', 'Shipped', 'Cancelled'])]]);
        $order = Order::with(['customer', 'orderItems.product'])->findOrFail($order_id);
        
        // Security Check for Order
        if (!Auth::user()->isSuperAdmin()) {
            $hasAccess = $order->orderItems->contains(function ($item) {
                return $item->product && Auth::user()->events->contains('event_id', $item->product->event_id);
            });

            if (!$hasAccess) {
                abort(403, 'Order ini tidak terkait dengan event Anda.');
            }
        }
        
        if ($order->status !== $request->status) {
            $oldStatus = $order->status;
            $newStatus = $request->status;
            
            // PERBAIKAN: LOGIKA RESTORE STOCK JIKA CANCELLED (Berlaku untuk SEMUA tipe produk)
            if ($newStatus === 'Cancelled' && $oldStatus !== 'Cancelled') {
                foreach ($order->orderItems as $item) {
                    if ($item->product) {
                        // Kondisi pengecekan tipe 'Fisik' dihapus agar Digital juga dikembalikan stoknya
                        $item->product->increment('stok', $item->kuantitas);
                    }
                }
                // Restore Kuota Diskon
                if ($order->diskon_code) {
                    $discount = \App\Models\Discount::where('code', $order->diskon_code)->first();
                    if ($discount) $discount->decrement('used_count');
                }
            }

            $order->status = $newStatus;
            $order->save();

            // Kirim email konfirmasi jika status berubah menjadi 'Paid'
            if ($newStatus === 'Paid' && $order->customer && $order->customer->email) {
                try {
                    Mail::to($order->customer->email)->send(new OrderConfirmation($order));
                } catch (\Exception $e) {
                    Log::error('Gagal kirim email order #'.$order->order_id.': '.$e->getMessage());
                }
            }
        }

        return back()->with('success', 'Status order berhasil diperbarui.');
    }

    /**
     * Resend ticket email to customer (Superadmin only)
     */
    public function resendTicket($order_id)
    {
        // Check if user is Superadmin
        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk fitur ini.');
        }

        $order = Order::with(['customer', 'orderItems.product'])->where('order_id', $order_id)->first();

        if (!$order) {
            return back()->with('error', 'Order tidak ditemukan.');
        }

        if ($order->status !== 'Paid') {
            return back()->with('error', 'Hanya order dengan status Paid yang bisa dikirim ulang tiketnya.');
        }

        if (!$order->customer || !$order->customer->email) {
            return back()->with('error', 'Customer tidak memiliki email yang valid.');
        }

        try {
            // Generate tickets if not exist
            foreach ($order->orderItems as $orderItem) {
                if (!$orderItem->product || !in_array($orderItem->product->tipe, ['Digital', 'Seminar'])) {
                    continue;
                }
                
                if ($orderItem->tickets()->count() === 0) {
                    \App\Models\Ticket::createForOrderItem($orderItem);
                }
            }

            // Send email
            Mail::to($order->customer->email)->send(new OrderConfirmation($order));
            
            Log::info("Ticket resent by Superadmin to: {$order->customer->email} for Order: {$order->order_id}");
            
            return back()->with('success', "Email tiket berhasil dikirim ulang ke {$order->customer->email}");
        } catch (\Exception $e) {
            Log::error("Resend ticket failed for Order {$order->order_id}: " . $e->getMessage());
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 5. PENGATURAN (SUPERADMIN)
    // =========================================================================

    public function settings()
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        $settings = Setting::all()->pluck('value', 'key');
        $events = \App\Models\Event::orderBy('created_at', 'desc')->get(); // For reset feature
        return view('admin.settings', compact('settings', 'events'));
    }

    public function resetTransactions(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);

        $request->validate([
            'password' => 'required',
            'event_id' => 'required',
        ]);

        // 1. Verify Password
        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Password konfirmasi salah. Data tidak dihapus.');
        }

        // 2. Perform Reset
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            if ($request->event_id === 'all') {
                // Hapus SEMUA Data Transaksi
                OrderItem::truncate();
                \App\Models\Order::truncate();
                // Opsional: Truncate Payments/Xendit logs jika ada
            } else {
                // Hapus Data Spesifik Event
                $eventId = $request->event_id;
                
                // Cari item yang berhubungan dengan produk dari event ini
                $items = OrderItem::whereHas('product', function($q) use ($eventId) {
                    $q->where('event_id', $eventId);
                })->get();

                // Kumpulkan Order ID yang terdampak untuk dicek nanti
                $orderIdsToCheck = $items->pluck('order_id')->unique();

                // Hapus Items
                foreach ($items as $item) {
                    $item->delete();
                }

                // Cek Orders: Jika tidak punya item lagi, hapus Order-nya
                foreach ($orderIdsToCheck as $orderId) {
                    $order = \App\Models\Order::find($orderId); // Pake find karena order_id string/primary
                    if ($order) {
                        // Cek apakah masih ada item tersisa
                        if ($order->orderItems()->count() == 0) {
                            $order->delete();
                        } else {
                            // Recalculate total logic could go here if needed, 
                            // but safest to just delete order if empty for this 'reset' purpose.
                        }
                    }
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('success', 'Data transaksi berhasil di-reset bersih.');

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Safety return
            return back()->with('error', 'Gagal mereset data: ' . $e->getMessage());
        }
    }

    public function updateSettings(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);

        $keys = [
            'site_title', 'primary_color', 'secondary_color', 'header_bg_color', 
            'header_text_color', 'footer_bg_color', 'footer_text_color',
            'footer_blog_link', 'footer_career_link', 'footer_contact_link', 'footer_faq_link',
            'social_instagram', 'social_twitter', 'social_facebook', 'social_linkedin'
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
            }
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            $oldLogo = Setting::where('key', 'logo_path')->first();
            if ($oldLogo && $oldLogo->value) Storage::disk('public')->delete($oldLogo->value);
            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $path, 'type' => 'image']);
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('settings', 'public');
            $oldFavicon = Setting::where('key', 'favicon_path')->first();
            if ($oldFavicon && $oldFavicon->value) Storage::disk('public')->delete($oldFavicon->value);
            Setting::updateOrCreate(['key' => 'favicon_path'], ['value' => $path, 'type' => 'image']);
        }

        // Organizer Banner
        if ($request->hasFile('organizer_banner')) {
            $path = $request->file('organizer_banner')->store('settings', 'public');
            $oldBanner = Setting::where('key', 'organizer_banner_path')->first();
            if ($oldBanner && $oldBanner->value) Storage::disk('public')->delete($oldBanner->value);
            Setting::updateOrCreate(['key' => 'organizer_banner_path'], ['value' => $path, 'type' => 'image']);
        }

        return redirect()->route('admin.settings')->with('success', 'Pengaturan berhasil diperbarui!');
    }

    public function transactionSettings()
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.transaction_settings', compact('settings'));
    }

    public function updateTransactionSettings(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);

        $keys = [
            'tax_admin_fee_percentage',
            'tax_admin_fee_min', 
            'tax_ppn_percentage',
            'tax_service_fee_va',
            'tax_service_fee_gopay',
            'tax_service_fee_qris'
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
            }
        }

        return redirect()->route('admin.transaction.settings')->with('success', 'Pengaturan Biaya & Pajak berhasil diperbarui.');
    }

    // =========================================================================
    // 6. ORGANIZER PROFILE
    // =========================================================================

    /**
     * Tampilkan form edit profil organizer
     */
    public function showOrganizerProfileForm()
    {
        return view('admin.organizer_profile');
    }

    /**
     * Update profil organizer
     */
    public function updateOrganizerProfile(Request $request)
    {
        $request->validate([
            'organizer_name' => 'required|string|max:100',
            'organizer_slug' => 'nullable|string|max:100|unique:users,organizer_slug,' . auth()->id(),
            'organizer_description' => 'nullable|string|max:1000',
            'organizer_email' => 'nullable|email|max:100',
            'organizer_phone' => 'nullable|string|max:20',
            'organizer_city' => 'nullable|string|max:50',
            'organizer_instagram' => 'nullable|url|max:100',
            'organizer_logo' => 'nullable|image|max:2048',
        ]);

        $user = auth()->user();

        // Handle logo upload
        if ($request->hasFile('organizer_logo')) {
            // Delete old logo if exists
            if ($user->organizer_logo && !str_starts_with($user->organizer_logo, 'http')) {
                Storage::disk('public')->delete($user->organizer_logo);
            }
            $logoPath = $request->file('organizer_logo')->store('organizers', 'public');
            $user->organizer_logo = $logoPath;
        }

        // Update fields
        $user->organizer_name = $request->organizer_name;
        $user->organizer_description = $request->organizer_description;
        $user->organizer_email = $request->organizer_email;
        $user->organizer_phone = $request->organizer_phone;
        $user->organizer_city = $request->organizer_city;
        $user->organizer_instagram = $request->organizer_instagram;

        // Handle slug
        if ($request->filled('organizer_slug')) {
            $user->organizer_slug = \Illuminate\Support\Str::slug($request->organizer_slug);
        } else {
            // Auto-generate slug from organizer_name
            $user->generateOrganizerSlug();
        }

        $user->save();

        return back()->with('success', 'Profil Organizer berhasil disimpan!');
    }

    /**
     * Export Event Participants to Excel/CSV
     */
    public function exportEventParticipants(Request $request, $event_id)
    {
        $event = Event::findOrFail($event_id);
        
        // Security Check
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->events->contains('event_id', $event_id)) {
            abort(403, 'Anda tidak memiliki akses ke event ini.');
        }

        // Create filename first
        $filename = 'peserta_' . \Illuminate\Support\Str::slug($event->judul) . '_' . date('Y-m-d') . '.xls';
        
        // 1. Get Product IDs using DB Facade (Safe from Model issues)
        $productIdArray = DB::table('products')
            ->where('event_id', $event_id)
            ->pluck('product_id')
            ->toArray();

        // 2. Find Orders containing these products
        // Simplified query to ensure execution
        $orders = Order::whereHas('orderItems', function($query) use ($productIdArray) {
                $query->whereIn('product_id', $productIdArray);
            })
            ->with(['customer', 'orderItems.product']) // Removed closure to avoid 500 error on eager load
            ->where('status', 'Paid')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get event's custom form fields configuration
        $customFormFields = $event->custom_form_fields ?? [];
        
        // Build headers
        $headers = ['No', 'Order ID', 'Nama Peserta', 'Email', 'No. HP', 'Produk/Tiket', 'Jumlah', 'Tanggal Order'];
        
        // Add NIK, DOB, Gender if buyer form has them
        $buyerFormFields = $event->buyer_form_fields ?? [];
        $hasNik = false;
        $hasDob = false;
        $hasGender = false;
        
        foreach ($buyerFormFields as $field) {
            if (($field['field'] ?? '') === 'nik' && ($field['enabled'] ?? false)) {
                $hasNik = true;
                $headers[] = 'NIK';
            }
            if (($field['field'] ?? '') === 'dob' && ($field['enabled'] ?? false)) {
                $hasDob = true;
                $headers[] = 'Tanggal Lahir';
            }
            if (($field['field'] ?? '') === 'gender' && ($field['enabled'] ?? false)) {
                $hasGender = true;
                $headers[] = 'Jenis Kelamin';
            }
        }
        
        // Add custom form field headers
        foreach ($customFormFields as $cf) {
            $headers[] = $cf['label'] ?? $cf['name'] ?? 'Custom Field';
        }

        // Build rows
        $rows = [];
        $no = 1;
        
        foreach ($orders as $order) {
            $customer = $order->customer;
            $buyerCustomData = is_array($order->buyer_custom_data) ? $order->buyer_custom_data : [];
            
            // Get items from this event only (using filtered product IDs)
            $eventItems = $order->orderItems; // Ambil semua dulu
            
            foreach ($eventItems as $item) {
                // Manual filter di loop
                if (!in_array($item->product_id, $productIdArray)) {
                    continue;
                }
                
                $productName = '-';
                if ($item->product) {
                    $productName = $item->product->nama_produk;
                } else {
                    // Try to fetch via DB if relation failed (soft delete issue)
                    $p = DB::table('products')->where('product_id', $item->product_id)->first();
                    if ($p) $productName = $p->nama_produk . ' (Deleted)';
                }

                $row = [
                    $no++,
                    $order->order_id,
                    $customer->name ?? '-',
                    $customer->email ?? '-',
                    $customer->phone ?? '-',
                    $productName,
                    $item->kuantitas,
                    optional($order->created_at)->format('Y-m-d H:i') ?? '-',
                ];
                
                // Add NIK, DOB, Gender if enabled
                if ($hasNik) {
                    $row[] = $order->buyer_nik ?? '-';
                }
                if ($hasDob) {
                    $row[] = $order->buyer_dob ?? '-';
                }
                if ($hasGender) {
                    $genderMap = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
                    $row[] = $genderMap[$order->buyer_gender] ?? ($order->buyer_gender ?? '-');
                }
                
                // Add custom field values
                foreach ($customFormFields as $cf) {
                    $fieldName = 'custom_field_' . ($cf['name'] ?? '');
                    $val = $buyerCustomData[$fieldName] ?? '-';
                    
                    // Handle array/object values safely to avoid 500 Error (Array to string conversion)
                    if (is_array($val)) {
                        $val = implode(', ', $val);
                    } elseif (is_object($val)) {
                        $val = json_encode($val);
                    }
                    
                    $row[] = $val;
                }
                
                $rows[] = $row;
            }
        }

        // Generate filename dengan ekstensi .xls agar langsung terbuka di Excel
        // $filename sudah didefinisikan diatas

        // Generate HTML table yang bisa dibuka oleh Excel dengan kolom terpisah
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; font-weight: bold; }
    </style>
</head>
<body>
<table>';

        // Header row
        $html .= '<tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars((string)$header) . '</th>';
        }
        $html .= '</tr>';

        // Data rows
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}