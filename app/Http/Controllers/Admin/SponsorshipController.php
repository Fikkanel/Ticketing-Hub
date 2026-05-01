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

class SponsorshipController extends Controller
{
    public function index()
    {
        return "Sponsorship index reached";
        // $sponsorships = Sponsorship::with(['event', 'order'])->latest()->paginate(10);
        // return view('admin.sponsorship.index', compact('sponsorships'));
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
                ['email' => 'sponsor@tixkita.id'],
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
        $sponsorship->load(['order.orderItems.tickets']);
        $tickets = $sponsorship->order->orderItems->first()->tickets;

        $callback = function() use ($tickets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Ticket Code', 'Access Link']);

            foreach ($tickets as $index => $ticket) {
                fputcsv($file, [
                    $index + 1,
                    $ticket->ticket_code,
                    'https://access.tixkita.id/' . $ticket->secret_token
                ]);
            }

            fclose($file);
        };

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sponsorship_' . Str::slug($sponsorship->name) . '.csv"',
        ];

        return response()->stream($callback, 200, $headers);
    }
}
