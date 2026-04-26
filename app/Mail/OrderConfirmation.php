<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Instance Order yang dikirim dari Controller.
     * @var \App\Models\Order
     */
    public $order;

    /**
     * Create a new message instance.
     *
     * @param Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     * Mengirim email dengan data tambahan (digitalItems & hasPhysical).
     *
     * @return $this
     */
    public function build()
    {
        // 1. Filter Item Digital (Tiket/Voucher) - include 'Digital' dan 'Seminar'
        $digitalItems = $this->order->orderItems->filter(function ($item) {
            return $item->product && in_array($item->product->tipe, ['Digital', 'Seminar']);
        });

        // 2. Cek barang Fisik
        $hasPhysical = $this->order->orderItems->contains(function ($item) {
            return $item->product && $item->product->tipe === 'Fisik';
        });

        // 3. Check if this order contains Seminar products
        $seminarLink = null;
        $isSeminar = false;
        foreach ($this->order->orderItems as $item) {
            // Deteksi seminar berdasarkan tipe produk 'Seminar'
            if ($item->product && $item->product->tipe === 'Seminar') {
                $isSeminar = true;
                // Prioritas: 1. Product whatsapp_link, 2. Event seminar_whatsapp_link, 3. Admin WhatsApp
                if (!empty($item->product->whatsapp_link)) {
                    $seminarLink = $item->product->whatsapp_link;
                } elseif ($item->product->event && !empty($item->product->event->seminar_whatsapp_link)) {
                    $seminarLink = $item->product->event->seminar_whatsapp_link;
                } else {
                    // Fallback ke admin whatsapp dari settings
                    $adminWa = \App\Models\Setting::get('admin_whatsapp', null);
                    if ($adminWa) {
                        $seminarLink = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $adminWa);
                    }
                }
                break;
            }
        }

        $email = $this->subject('Konfirmasi Pesanan #' . $this->order->order_id)
                      ->view('emails.order_confirmation')
                      ->with([
                          'digitalItems' => $digitalItems,
                          'hasPhysical' => $hasPhysical,
                          'isSeminar' => $isSeminar,
                          'seminarLink' => $seminarLink,
                      ]);

        // 4. Generate & Attach PDF Ticket jika ada item digital DAN BUKAN SEMINAR
        // Seminar tidak mengirim tiket PDF, hanya link WhatsApp
        if ($digitalItems->count() > 0 && !$isSeminar) {
            try {
                // Pastikan dompdf sudah terinstall: composer require barryvdh/laravel-dompdf
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket', ['order' => $this->order, 'items' => $digitalItems]);
                $pdf->setPaper('a4', 'portrait');

                $email->attachData($pdf->output(), 'Tiket-Order-'.$this->order->order_id.'.pdf', [
                    'mime' => 'application/pdf',
                ]);
            } catch (\Exception $e) {
                // Fallback jika PDF gagal generate (misal library belum ada/error)
                \Illuminate\Support\Facades\Log::error('Gagal generate PDF Tiket: ' . $e->getMessage());
            }
        }

        return $email;
    }
}