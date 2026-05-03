<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ScannerController extends Controller
{
    /**
     * Show Scanner PWA Interface.
     */
    public function index()
    {
        return view('scanner.index');
    }

    /**
     * Process QR Code Scan via API.
     */
    public function process(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $qrCode = $request->qr_code;
        
        // 2. Parse Code: Expected format "TIKET-{ITEM_ID}-{SEQUENCE}" or legacy "TIKET-{ITEM_ID}"
        $parsed = Ticket::parseCode($qrCode);
        
        if (!$parsed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format QR Code tidak valid.'
            ], 400);
        }

        $itemId = $parsed['item_id'];
        $sequence = $parsed['sequence'];

        // 3. Find the ticket or order item
        if ($sequence !== null) {
            // New format: Look up individual ticket
            $ticket = Ticket::with(['orderItem.order.customer', 'orderItem.product.event'])
                ->where('ticket_code', $qrCode)
                ->first();

            if (!$ticket) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tiket tidak ditemukan.'
                ], 404);
            }

            $orderItem = $ticket->orderItem;
            $order = $orderItem->order;
            $product = $orderItem->product;

            // 4. Check Order Status
            if ($order->status !== 'Paid') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order belum lunas. Status: ' . $order->status
                ], 400);
            }

            // 4.5 Check Access Authorization (Scanner restriction)
            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->hasAccessToEvent($product->event_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'AKSES DITOLAK: Anda tidak memiliki izin untuk menscan tiket event ini.'
                ], 403);
            }

            // 4.6 Check Date Validity
            if ($product->valid_date && $product->valid_date->format('Y-m-d') !== now()->format('Y-m-d')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tiket ini HANYA berlaku untuk tanggal ' . $product->valid_date->format('d M Y') . ' (Hari ini: ' . now()->format('d M Y') . ')'
                ], 400);
            }

            // 5. Check if Already Scanned
            if ($ticket->is_scanned) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Tiket SUDAH PERNAH digunakan.',
                    'data' => [
                        'scanned_at' => $ticket->scanned_at->format('d M Y H:i'),
                        'scanned_by' => $ticket->scannedBy->name ?? 'Unknown',
                        'product_name' => $product->nama_produk,
                        'customer_name' => $order->customer->name,
                        'ticket_code' => $ticket->ticket_code,
                    ]
                ], 200);
            }

            // 6. Mark as Scanned
            try {
                $ticket->is_scanned = true;
                $ticket->scanned_at = now();
                $ticket->scanned_by = Auth::id();
                $ticket->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Scan Berhasil! Silakan Masuk.',
                    'data' => [
                        'product_name' => $product->nama_produk,
                        'customer_name' => $order->customer->name,
                        'order_id' => $order->order_id,
                        'ticket_code' => $ticket->ticket_code,
                        'ticket_number' => $ticket->sequence . ' of ' . $orderItem->kuantitas,
                        'ticket_type' => $product->event->ticket_type ?? 'E-TICKET'
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('Scan Error: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan sistem saat update data.'
                ], 500);
            }

        } else {
            // Legacy format: "TIKET-{ITEM_ID}" - check if tickets exist for this order item
            $orderItem = OrderItem::with('order.customer', 'product.event', 'tickets')->find($itemId);

            if (!$orderItem) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tiket tidak ditemukan.'
                ], 404);
            }

            // Check if individual tickets exist
            if ($orderItem->tickets->count() > 0) {
                // Individual tickets exist - tell user to scan specific ticket
                $unscanned = $orderItem->tickets->where('is_scanned', false)->count();
                $total = $orderItem->tickets->count();
                
                return response()->json([
                    'status' => 'warning',
                    'message' => "Tiket ini memiliki {$total} sub-tiket. Silakan scan kode individual (contoh: TIKET-{$itemId}-1). Tersisa {$unscanned} tiket belum di-scan.",
                    'data' => [
                        'product_name' => $orderItem->product->nama_produk,
                        'customer_name' => $orderItem->order->customer->name ?? 'Guest',
                        'tickets_remaining' => $unscanned,
                        'tickets_total' => $total,
                    ]
                ], 200);
            }

            // No individual tickets - fallback to legacy behavior (mark order item as scanned)
            $order = $orderItem->order;
            $product = $orderItem->product;

            if ($order->status !== 'Paid') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order belum lunas. Status: ' . $order->status
                ], 400);
            }

            $user = Auth::user();
            if (!$user->isSuperAdmin() && !$user->hasAccessToEvent($product->event_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'AKSES DITOLAK: Anda tidak memiliki izin untuk menscan tiket event ini.'
                ], 403);
            }

            // 4.6 Check Date Validity (Legacy Mode)
            if ($product->valid_date && $product->valid_date->format('Y-m-d') !== now()->format('Y-m-d')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tiket ini HANYA berlaku untuk tanggal ' . $product->valid_date->format('d M Y') . ' (Hari ini: ' . now()->format('d M Y') . ')'
                ], 400);
            }

            if ($orderItem->is_scanned) {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'Tiket SUDAH PERNAH digunakan.',
                    'data' => [
                        'scanned_at' => $orderItem->scanned_at->format('d M Y H:i'),
                        'scanned_by' => $orderItem->scannedBy->name ?? 'Unknown',
                        'product_name' => $product->nama_produk,
                        'customer_name' => $order->customer->name
                    ]
                ], 200);
            }

            try {
                $orderItem->is_scanned = true;
                $orderItem->scanned_at = now();
                $orderItem->scanned_by = Auth::id();
                $orderItem->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Scan Berhasil! Silakan Masuk.',
                    'data' => [
                        'product_name' => $product->nama_produk,
                        'customer_name' => $order->customer->name,
                        'order_id' => $order->order_id,
                        'ticket_type' => $product->event->ticket_type ?? 'E-TICKET'
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('Scan Error: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan sistem saat update data.'
                ], 500);
            }
        }
    }
}
