<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\MidtransService;
use App\Mail\OrderConfirmation;
use Midtrans\Config;
use Midtrans\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // Configure Midtrans (ensure configs are loaded)
        $midtrans = new MidtransService(); 

        try {
            $notification = new Notification();

            $transaction = $notification->transaction_status;
            $type = $notification->payment_type;
            $orderId = $notification->order_id;
            $fraud = $notification->fraud_status;

            Log::info("Midtrans Callback: Order $orderId Status $transaction");

            $order = Order::where('order_id', $orderId)->first();

            if (!$order) {
                // Return 200 OK even if order not found (for Midtrans test notifications)
                Log::info("Midtrans Callback: Order $orderId not found (possibly test notification)");
                return response()->json(['message' => 'Order not found, but acknowledged'], 200);
            }

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $order->update(['status' => 'Pending']); // Challenge by FDS
                    } else {
                        $order->update(['status' => 'Paid']);
                        $this->generateTicketsForOrder($order);
                        $this->sendConfirmationEmail($order);
                    }
                }
            } else if ($transaction == 'settlement') {
                $order->update(['status' => 'Paid']);
                $this->generateTicketsForOrder($order);
                $this->sendConfirmationEmail($order);
            } else if ($transaction == 'pending') {
                $order->update(['status' => 'Pending']);
            } else if ($transaction == 'deny') {
                $order->update(['status' => 'Failed']);
            } else if ($transaction == 'expire') {
                $order->update(['status' => 'Expired']);
            } else if ($transaction == 'cancel') {
                $order->update(['status' => 'Cancelled']);
            }

            return response()->json(['message' => 'Callback processed']);

        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
            // Return 200 OK even on error to acknowledge receipt (especially for test notifications)
            // Test notifications from Midtrans use fake transaction data which causes "Transaction doesn't exist" error
            return response()->json(['message' => 'Callback acknowledged with error: ' . $e->getMessage()], 200);
        }
    }

    /**
     * Generate tickets for order after payment is confirmed
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
            Log::info("Tickets generated for OrderItem: {$orderItem->item_id}");
        }
    }

    /**
     * Send confirmation email with tickets to customer
     */
    protected function sendConfirmationEmail(Order $order)
    {
        try {
            $order->load('customer');
            
            if ($order->customer && $order->customer->email) {
                Mail::to($order->customer->email)->send(new OrderConfirmation($order));
                Log::info("Confirmation email sent to: {$order->customer->email} for Order: {$order->order_id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send confirmation email for Order {$order->order_id}: " . $e->getMessage());
        }
    }
}
