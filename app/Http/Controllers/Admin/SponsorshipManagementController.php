<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\Sponsorship;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SponsorshipManagementController extends Controller
{
    public function index()
    {
        $sponsorships = Sponsorship::with(['event', 'order'])->latest()->paginate(10);
        return view('admin.sponsorship.index', compact('sponsorships'));
    }

    public function create()
    {
        $events = Event::where('status', '!=', 'Finished')->get();
        return view('admin.sponsorship.create', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,event_id',
            'product_id' => 'required|exists:products,product_id',
            'name' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1|max:10000',
        ]);

        DB::beginTransaction();

        try {
            // 1. Get or Create Sponsorship Customer
            $customer = Customer::firstOrCreate(
                ['email' => 'sponsor@Ticketing Hub.id'],
                [
                    'name' => 'Sponsorship User',
                    'phone' => '0000000000',
                    'password' => Hash::make(Str::random(16)),
                ]
            );

            // 2. Create Order
            $order = Order::create([
                'customer_id' => $customer->id,
                'total_harga' => 0,
                'status' => 'Paid',
                'metode_pembayaran' => 'SPONSORSHIP',
                'tgl_order' => now(),
            ]);

            // 3. Create OrderItem
            $product = Product::findOrFail($request->product_id);
            $orderItem = OrderItem::create([
                'order_id' => $order->order_id,
                'product_id' => $product->product_id,
                'kuantitas' => $request->quantity,
                'subtotal' => 0,
            ]);

            // 4. Create Tickets with Secret Tokens
            for ($i = 1; $i <= $request->quantity; $i++) {
                Ticket::create([
                    'ticket_code' => Ticket::generateCode($orderItem->item_id, $i),
                    'secret_token' => Str::random(40),
                    'order_item_id' => $orderItem->item_id,
                    'sequence' => $i,
                ]);
            }

            // 5. Save Sponsorship Record
            Sponsorship::create([
                'event_id' => $request->event_id,
                'name' => $request->name,
                'quota' => $request->quantity,
                'order_id' => $order->order_id,
            ]);

            DB::commit();

            return redirect()->route('admin.sponsorships.index')->with('success', 'Tiket sponsorship berhasil di-generate.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal generate tiket: ' . $e->getMessage());
        }
    }

    public function export(Sponsorship $sponsorship)
    {
        $sponsorship->load(['event', 'order.orderItems.tickets']);
        $tickets = $sponsorship->order->orderItems->first()->tickets;
        $eventName = $sponsorship->event->judul ?? '-';
        $sponsorName = $sponsorship->name;

        $callback = function() use ($tickets, $sponsorName, $eventName) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM agar Excel langsung mengenali encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header kolom (semicolon delimiter agar Excel auto-split)
            fputcsv($file, [
                'No', 
                'Ticket Code', 
                'Access Link', 
                'Nama Sponsor', 
                'Event', 
                'Status Scan'
            ], ';');

            foreach ($tickets as $index => $ticket) {
                fputcsv($file, [
                    $index + 1,
                    $ticket->ticket_code,
                    'https://access.Ticketing Hub.id/' . $ticket->secret_token,
                    $sponsorName,
                    $eventName,
                    $ticket->is_scanned ? 'Sudah Scan' : 'Belum Scan',
                ], ';');
            }

            fclose($file);
        };

        $filename = 'sponsorship_' . Str::slug($sponsorName) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream($callback, 200, $headers);
    }
}
