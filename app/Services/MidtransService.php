<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\CoreApi;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        $this->configureMidtrans();
    }

    protected function configureMidtrans()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    /**
     * Create QRIS transaction directly via Core API
     * Returns QR code URL and actions for payment
     */
    public function createQrisTransaction($order, $customer)
    {
        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $order->order_id ?? $order->id,
                'gross_amount' => (int) $order->total_harga,
            ],
            'customer_details' => [
                'first_name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ],
            'item_details' => $this->mapOrderItems($order),
            // 'qris' => [
            //     'acquirer' => 'gopay', // Optional: Let Midtrans decide default acquirer (Sandbox usually supports generic/gopay)
            // ],
        ];

        try {
            $response = CoreApi::charge($params);
            
            Log::info('QRIS Transaction Created:', (array) $response);
            
            return [
                'success' => true,
                'transaction_id' => $response->transaction_id ?? null,
                'order_id' => $response->order_id ?? ($order->order_id ?? $order->id),
                'qr_string' => $response->qr_string ?? null,
                'qr_url' => $response->actions[0]->url ?? null, // QR code URL
                'expire_time' => $response->expiry_time ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans QRIS Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createSnapTransaction($order, $customer, $enabledPayments = [])
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_id ?? $order->id, // Fallback if order_id is not set
                'gross_amount' => (int) $order->total_harga,
            ],
            'customer_details' => [
                'first_name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ],
            'item_details' => $this->mapOrderItems($order),
            'callbacks' => [
                'finish' => route('public.invoice', $order->order_id ?? $order->id),
            ],
        ];

        // LOGIC PAYMENT CHANNELS
        if (!empty($enabledPayments)) {
            $params['enabled_payments'] = $enabledPayments;
        } else {
             // Fallback Logic (Legacy behavior for partial support)
             // Ambil Event dari Order Item pertama
             $firstItem = $order->orderItems->first();
             if ($firstItem && $firstItem->product && $firstItem->product->event) {
                 $event = $firstItem->product->event;
                 if ($event->payment_channels === 'qris_only') {
                     $params['enabled_payments'] = ['other_qris'];
                 }
             }
        }

        try {
            // Get Snap Token
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return null;
        }
    }

    protected function mapOrderItems($order)
    {
        $items = [];
        $itemsTotal = 0;
        
        foreach ($order->orderItems as $item) {
            $unitPrice = (int) round($item->subtotal / $item->kuantitas);
            $itemsTotal += $unitPrice * (int) $item->kuantitas;
            
            $items[] = [
                'id' => $item->product_id,
                'price' => $unitPrice,
                'quantity' => (int) $item->kuantitas,
                'name' => substr($item->product->nama_produk ?? 'Product', 0, 50),
            ];
        }

        // Add discount as a negative item if applicable
        if ($order->diskon_amount > 0) {
            $discountAmount = (int) round($order->diskon_amount);
            $itemsTotal -= $discountAmount;
             $items[] = [
                'id' => 'DISCOUNT',
                'price' => -$discountAmount,
                'quantity' => 1,
                'name' => 'Diskon Coupon',
            ];
        }

        // Add Fees
        if ($order->fee_admin > 0) {
            $adminFee = (int) round($order->fee_admin);
            $itemsTotal += $adminFee;
            $items[] = [
                'id' => 'ADMIN-FEE',
                'price' => $adminFee,
                'quantity' => 1,
                'name' => 'Biaya Admin',
            ];
        }
        if ($order->fee_service > 0) {
            $serviceFee = (int) round($order->fee_service);
            $itemsTotal += $serviceFee;
            $items[] = [
                'id' => 'SERVICE-FEE',
                'price' => $serviceFee,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];
        }
        if ($order->fee_tax > 0) {
            $taxFee = (int) round($order->fee_tax);
            $itemsTotal += $taxFee;
            $items[] = [
                'id' => 'TAX-PPN',
                'price' => $taxFee,
                'quantity' => 1,
                'name' => 'PPN (11%)',
            ];
        }

        // Add rounding adjustment if there's a mismatch
        $grossAmount = (int) $order->total_harga;
        $difference = $grossAmount - $itemsTotal;
        
        if ($difference != 0) {
            $items[] = [
                'id' => 'ROUNDING',
                'price' => $difference,
                'quantity' => 1,
                'name' => 'Pembulatan',
            ];
        }

        return $items;
    }
}
