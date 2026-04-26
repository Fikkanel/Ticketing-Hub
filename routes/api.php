<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/midtrans/callback', [App\Http\Controllers\PaymentCallbackController::class, 'handle']);

// Bundle stock validation API
Route::get('/check-bundle-stock/{bundleId}', function ($bundleId) {
    $bundle = App\Models\Bundle::with('items.product')->find($bundleId);
    
    if (!$bundle) {
        return response()->json(['available' => false, 'error' => 'Bundle tidak ditemukan']);
    }
    
    if ($bundle->stok <= 0) {
        return response()->json(['available' => false, 'error' => 'Stok bundle habis']);
    }
    
    $insufficientProducts = [];
    foreach ($bundle->items as $item) {
        if (!$item->product || $item->product->stok < $item->quantity) {
            $insufficientProducts[] = [
                'name' => $item->product->nama_produk ?? 'Unknown',
                'available' => $item->product->stok ?? 0,
                'required' => $item->quantity
            ];
        }
    }
    
    if (count($insufficientProducts) > 0) {
        $errorMsg = 'Stok produk tidak mencukupi: ';
        foreach ($insufficientProducts as $p) {
            $errorMsg .= $p['name'] . ' (tersedia: ' . $p['available'] . ', butuh: ' . $p['required'] . '), ';
        }
        return response()->json(['available' => false, 'error' => rtrim($errorMsg, ', ')]);
    }
    
    return response()->json(['available' => true, 'stock' => $bundle->stok]);
});

// Get purchase limits for cart items
Route::post('/get-cart-limits', function (Request $request) {
    $productIds = $request->input('product_ids', []);
    $bundleIds = $request->input('bundle_ids', []);
    
    $limits = [];
    $events = [];
    
    // Get limits from products
    if (!empty($productIds)) {
        $products = App\Models\Product::with('event')->whereIn('product_id', $productIds)->get();
        foreach ($products as $product) {
            if ($product->event) {
                $eventId = $product->event->event_id;
                $maxTickets = $product->event->max_tickets_per_transaction ?? 10;
                
                if (!isset($events[$eventId])) {
                    $events[$eventId] = [
                        'max_tickets' => $maxTickets,
                        'limit_one_email' => $product->event->limit_one_email_per_transaction ?? false,
                        'products' => []
                    ];
                }
                $events[$eventId]['products'][] = $product->product_id;
                
                $limits[$product->product_id] = [
                    'event_id' => $eventId,
                    'max_tickets' => $maxTickets,
                    'stock' => $product->stok
                ];
            }
        }
    }
    
    // Get limits from bundles
    if (!empty($bundleIds)) {
        $bundles = App\Models\Bundle::with('event')->whereIn('id', $bundleIds)->get();
        foreach ($bundles as $bundle) {
            if ($bundle->event) {
                $eventId = $bundle->event->event_id;
                $maxTickets = $bundle->event->max_tickets_per_transaction ?? 10;
                
                if (!isset($events[$eventId])) {
                    $events[$eventId] = [
                        'max_tickets' => $maxTickets,
                        'limit_one_email' => $bundle->event->limit_one_email_per_transaction ?? false,
                        'products' => []
                    ];
                }
                
                $limits['bundle_' . $bundle->id] = [
                    'event_id' => $eventId,
                    'max_tickets' => $maxTickets,
                    'stock' => $bundle->stok
                ];
            }
        }
    }
    
    return response()->json([
        'limits' => $limits,
        'events' => $events
    ]);
});
