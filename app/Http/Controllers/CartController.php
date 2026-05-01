<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Bundle;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Get current cart data with fresh prices and stock
     */
    public function getData()
    {
        $cart = Session::get('cart_items', []);
        $enrichedCart = [];
        $totalQty = 0;
        $subtotal = 0;

        // --- OPTIMIZATION: Fetch all products and bundles in single queries ---
        $bundleIds = [];
        $productIds = [];
        
        foreach ($cart as $item) {
            $id = $item['id'];
            if (str_starts_with($id, 'bundle_')) {
                $bundleIds[] = str_replace('bundle_', '', $id);
            } else {
                $productIds[] = $id;
            }
        }

        $productsDb = collect();
        if (!empty($productIds)) {
            $productsDb = Product::whereIn('product_id', $productIds)->get()->keyBy('product_id');
        }

        $bundlesDb = collect();
        if (!empty($bundleIds)) {
            $bundlesDb = Bundle::whereIn('id', $bundleIds)->get()->keyBy('id');
        }
        // --------------------------------------------------------------------

        foreach ($cart as $key => $item) {
            $id = $item['id'];
            $qty = intval($item['qty']);
            
            // Item Data Container
            $itemData = null;
            $maxStock = 0;
            $price = 0;
            $name = '';
            
            if (str_starts_with($id, 'bundle_')) {
                // Handle Bundle
                $bundleId = str_replace('bundle_', '', $id);
                $bundle = $bundlesDb->get($bundleId);
                
                if ($bundle) {
                    $itemData = $bundle;
                    $maxStock = $bundle->stok;
                    $price = $bundle->price;
                    $name = $bundle->name;
                    $type = 'bundle';
                }
            } else {
                // Handle Product
                $product = $productsDb->get($id);
                
                if ($product) {
                    $itemData = $product;
                    $maxStock = $product->stok;
                    $price = $product->harga;
                    $name = $product->nama_produk;
                    $type = 'product';
                }
            }

            // Only add if item exists and has stock (optional: allowed to hold 0 stock if already in cart? maybe warn)
            if ($itemData) {
                // Validate stock (optional: adjust qty if exceeds stock? or just report)
                // For now, we enforce max stock
                if ($qty > $maxStock) {
                    $qty = $maxStock;
                    // Update session if changed
                    $cart[$key]['qty'] = $qty;
                }

                if ($qty > 0) {
                    $total = $price * $qty;
                    $subtotal += $total;
                    $totalQty += $qty;

                    $enrichedCart[] = [
                        'id' => $id,
                        'name' => $name,
                        'price' => $price,
                        'qty' => $qty,
                        'stock' => $maxStock,
                        'total' => $total,
                        'type' => $type,
                        'seat_ids' => $item['seat_ids'] ?? []
                    ];
                }
            } else {
                // Item no longer exists, remove from session
                unset($cart[$key]);
            }
        }

        // Save back to session in case of cleanup
        Session::put('cart_items', $cart);

        return response()->json([
            'items' => array_values($enrichedCart),
            'total_qty' => $totalQty,
            'subtotal' => $subtotal
        ]);
    }

    /**
     * Helper: Validate Event Limits & Stock
     */
    private function validateItemLimit($id, $qty, $currentCart)
    {
        // 1. Get Item & Event Data
        $item = null;
        $eventId = null;
        $maxTickets = 10; // Default
        $stock = 0;
        $name = '';

        if (str_starts_with($id, 'bundle_')) {
            $bundleId = str_replace('bundle_', '', $id);
            $bundle = Bundle::with('event')->find($bundleId);
            if ($bundle) {
                $item = $bundle;
                $stock = $bundle->stok;
                $name = $bundle->name;
                if ($bundle->event) {
                    $eventId = $bundle->event->event_id;
                    $maxTickets = $bundle->event->max_tickets_per_transaction ?? 10;
                }
            }
        } else {
            $product = Product::with('event')->find($id);
            if ($product) {
                $item = $product;
                $stock = $product->stok;
                $name = $product->nama_produk;
                if ($product->event) {
                    $eventId = $product->event->event_id;
                    $maxTickets = $product->event->max_tickets_per_transaction ?? 10;
                }
            }
        }

        if (!$item) {
            return ['valid' => false, 'message' => 'Item tidak ditemukan.'];
        }

        // 2. Check Stock
        if ($qty > $stock) {
            return ['valid' => false, 'message' => "$name: Stok tidak mencukupi. Tersedia: $stock"];
        }

        // 2.5 Check Seats Availability if provided
        // Seat validation is handled in add() directly because validateItemLimit doesn't receive seat_ids currently.


        // 3. Check Event Max Tickets (Aggregation)
        if ($eventId) {
            $totalForEvent = 0;
            
            // Collect IDs to fetch in one go
            $bIdsToCheck = [];
            $pIdsToCheck = [];
            foreach ($currentCart as $cartItem) {
                if ($cartItem['id'] === $id) continue;
                $otherId = $cartItem['id'];
                if (str_starts_with($otherId, 'bundle_')) {
                    $bIdsToCheck[] = str_replace('bundle_', '', $otherId);
                } else {
                    $pIdsToCheck[] = $otherId;
                }
            }
            
            $bEventMap = [];
            if (!empty($bIdsToCheck)) {
                $bEventMap = Bundle::whereIn('id', $bIdsToCheck)->pluck('event_id', 'id')->toArray();
            }
            $pEventMap = [];
            if (!empty($pIdsToCheck)) {
                $pEventMap = Product::whereIn('product_id', $pIdsToCheck)->pluck('event_id', 'product_id')->toArray();
            }

            foreach ($currentCart as $cartItem) {
                // Skip the current item being updated (we will add the new qty)
                if ($cartItem['id'] === $id) continue;

                // Check if other items belong to same event
                $otherId = $cartItem['id'];
                $otherQty = $cartItem['qty'];
                
                if (str_starts_with($otherId, 'bundle_')) {
                    $bId = str_replace('bundle_', '', $otherId);
                    if (isset($bEventMap[$bId]) && $bEventMap[$bId] == $eventId) {
                        $totalForEvent += $otherQty;
                    }
                } else {
                    if (isset($pEventMap[$otherId]) && $pEventMap[$otherId] == $eventId) {
                        $totalForEvent += $otherQty;
                    }
                }
            }

            // Add the new quantity we are trying to set
            $totalForEvent += $qty;

            if ($totalForEvent > $maxTickets) {
                return ['valid' => false, 'message' => "Maksimal $maxTickets tiket per transaksi untuk event ini."];
            }
        }

        return ['valid' => true];
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'qty' => 'required|integer|min:1',
            'seat_ids' => 'nullable|array'
        ]);

        $id = $request->id;
        $qty = $request->qty;
        $seatIds = $request->seat_ids ?? [];
        
        if (!empty($seatIds)) {
            $qty = count($seatIds);
            
            // Validate if seats are really available
            $seats = \App\Models\Seat::whereIn('id', $seatIds)->get();
            foreach ($seats as $seat) {
                if ($seat->isBooked()) {
                    return response()->json(['success' => false, 'message' => 'Kursi ' . $seat->seat_number . ' sudah dipesan orang lain.'], 400);
                }
            }
        }
        
        $cart = Session::get('cart_items', []);
        
        // Find existing qty
        $currentQty = 0;
        $index = -1;
        foreach ($cart as $key => $item) {
            if ($item['id'] === $id) {
                $currentQty = $item['qty'];
                $index = $key;
                break;
            }
        }

        $newQty = $currentQty + $qty;

        // VALIDATE
        $check = $this->validateItemLimit($id, $newQty, $cart);
        if (!$check['valid']) {
             return response()->json(['success' => false, 'message' => $check['message']], 400);
        }

        // UPDATE SESSION
        if ($index !== -1) {
            $cart[$index]['qty'] = $newQty;
            if (!empty($seatIds)) {
                $cart[$index]['seat_ids'] = array_merge($cart[$index]['seat_ids'] ?? [], $seatIds);
            }
        } else {
            $cart[] = [
                'id' => $id,
                'qty' => $qty,
                'seat_ids' => $seatIds
            ];
        }

        Session::put('cart_items', $cart);

        return response()->json(['success' => true, 'message' => 'Item ditambahkan ke keranjang.']);
    }

    /**
     * Update item quantity
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'qty' => 'required|integer|min:1'
        ]);

        $id = $request->id;
        $qty = $request->qty;
        
        $cart = Session::get('cart_items', []);
        $index = -1;

        foreach ($cart as $key => $item) {
            if ($item['id'] === $id) {
                $index = $key;
                break;
            }
        }

        if ($index === -1) {
            return response()->json(['success' => false, 'message' => 'Item tidak ada di keranjang.'], 404);
        }

        // VALIDATE
        $check = $this->validateItemLimit($id, $qty, $cart);
        if (!$check['valid']) {
             return response()->json(['success' => false, 'message' => $check['message']], 400);
        }

        $cart[$index]['qty'] = $qty;
        Session::put('cart_items', $cart);

        return response()->json(['success' => true]);
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request)
    {
        $request->validate(['id' => 'required|string']);
        
        $cart = Session::get('cart_items', []);
        $newCart = [];

        foreach ($cart as $item) {
            if ($item['id'] !== $request->id) {
                $newCart[] = $item;
            }
        }

        Session::put('cart_items', $newCart);

        return response()->json(['success' => true]);
    }

    /**
     * Clear all items
     */
    public function clear()
    {
        Session::forget('cart_items');
        return response()->json(['success' => true]);
    }
}
