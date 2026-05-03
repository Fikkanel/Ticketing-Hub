<!DOCTYPE html>
<html>
<head>
    <title>E-Ticket TixKita</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style>
        /* Reset & Base Styles */
        body { margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f8; -webkit-font-smoothing: antialiased; }
        table { border-collapse: collapse; width: 100%; }
        
        /* Container */
        .wrapper { max-width: 640px; margin: 0 auto; background: #f4f6f8; padding: 20px; }
        .main-container { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.08); }
        
        /* Header */
        .header { background: #ffffff; padding: 30px 40px; text-align: center; border-bottom: 1px solid #eeeeee; }
        .logo { color: #333; font-size: 24px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin: 0; }
        .order-id { color: #888; font-size: 14px; margin-top: 5px; }

        /* Content Text */
        .intro-text { padding: 30px 40px 10px 40px; color: #555; line-height: 1.6; font-size: 16px; }
        
        /* MODERN TICKET DESIGN */
        .ticket-wrapper { padding: 0 20px 20px 20px; }
        .ticket-table { 
            width: 100%; 
            max-width: 550px; 
            margin: 20px auto; 
            border-radius: 16px; 
            box-shadow: 0 10px 20px rgba(58, 125, 68, 0.2);
        }
        
        /* Ticket Left Side (Event Info) */
        .ticket-left {
            /* Gradient Background */
            background: linear-gradient(135deg, #3A7D44 0%, #2b5c32 100%);
            background-color: #3A7D44; /* Fallback */
            padding: 25px;
            color: #ffffff;
            width: 65%;
            vertical-align: top;
            border-right: 2px dashed rgba(255,255,255,0.3);
            position: relative;
        }
        
        .event-name { font-size: 22px; font-weight: 800; margin-bottom: 5px; line-height: 1.2; text-transform: uppercase; }
        .event-type { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; margin-bottom: 20px; display: block; }
        
        .ticket-meta { margin-top: 15px; }
        .meta-label { font-size: 10px; text-transform: uppercase; opacity: 0.7; display: block; margin-bottom: 2px; }
        .meta-value { font-size: 14px; font-weight: 600; margin-bottom: 10px; display: block; }

        /* Ticket Right Side (QR Code / Stub) */
        .ticket-right {
            background-color: #ffffff;
            width: 35%;
            padding: 20px;
            text-align: center;
            vertical-align: middle;
        }
        /* Font diperkecil agar kode panjang muat */
        .ticket-code { font-family: 'Courier New', monospace; font-size: 11px; color: #333; font-weight: bold; margin-top: 10px; display: block; word-break: break-all; }
        .admit-one { font-size: 10px; color: #888; text-transform: uppercase; letter-spacing: 2px; writing-mode: vertical-rl; text-orientation: mixed; }

        /* Invoice Table */
        .invoice-section { padding: 20px 40px 40px 40px; }
        .invoice-table th { text-align: left; padding: 10px 0; border-bottom: 2px solid #eee; color: #888; font-size: 12px; text-transform: uppercase; }
        .invoice-table td { padding: 12px 0; border-bottom: 1px solid #f5f5f5; color: #333; }
        .total-row td { border-top: 2px solid #eee; border-bottom: none; font-weight: bold; font-size: 18px; color: #3A7D44; padding-top: 20px; }

        /* Footer */
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
        
        /* Utilities */
        .btn-check { display: inline-block; background: #3A7D44; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-size: 14px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    
    <div class="wrapper">
        <div class="main-container">
            <div class="header">
                <h1 class="logo">TIXKITA</h1>
                
                <p class="order-id">Order ID: #{{ $order->order_id }}</p>
            </div>

            <div class="intro-text">
                <p>Hai <strong>{{ $order->customer->name }}</strong>,</p>
                @if($order->orderItems->first()->product->event->custom_email_content)
                    <p>{!! nl2br(str_replace(
                        ['{customer_name}', '{order_id}', '{event_name}'], 
                        [$order->customer->name, $order->order_id, $order->orderItems->first()->product->event->judul], 
                        e($order->orderItems->first()->product->event->custom_email_content)
                    )) !!}</p>
                @else
                    <p>Terima kasih! Pembayaran Anda telah berhasil dikonfirmasi. Berikut adalah E-Ticket Anda. Tunjukkan kode QR di pintu masuk.</p>
                @endif
            </div>

            @if(isset($isSeminar) && $isSeminar && isset($seminarLink))
                {{-- SEMINAR MODE: Show WhatsApp Join Button --}}
                <div style="background-color: #25D366; border: none; color: white; padding: 20px; border-radius: 8px; margin: 20px; text-align: center;">
                    <span style="font-size: 24px; margin-bottom: 10px; display: block;">📲</span>
                    <strong style="font-size: 16px; display: block; margin-bottom: 10px;">Gabung Grup Seminar</strong>
                    <p style="margin: 0 0 15px 0; font-size: 14px;">Klik tombol di bawah untuk bergabung ke grup WhatsApp seminar.</p>
                    <a href="{{ $seminarLink }}" target="_blank" style="display: inline-block; background: white; color: #25D366; padding: 12px 30px; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 14px;">
                        Gabung Grup WhatsApp
                    </a>
                </div>
            @elseif($digitalItems->isNotEmpty())
                {{-- REGULAR MODE: Show Ticket Attached Notice --}}
                <div style="background-color: #e9f5eb; border: 1px solid #c3e6cb; color: #155724; padding: 15px 20px; border-radius: 8px; margin: 20px; display: flex; align-items: flex-start;">
                    <div>
                        <strong>Tiket Anda Terlampir!</strong><br>
                        Silakan unduh file PDF yang terlampir di email ini untuk melihat E-Ticket Anda lengkap dengan QR Code unik.
                    </div>
                </div>
            @endif

            <div class="invoice-section">
                <h3 style="color: #333; font-size: 16px; margin-bottom: 15px;">Rincian Pembayaran</h3>
                <table class="invoice-table" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product->nama_produk }}</strong>
                                <br><span style="font-size: 12px; color: #888;">x {{ $item->kuantitas }} @ Rp {{ number_format($item->product->harga, 0, ',', '.') }}</span>
                            </td>
                            <td style="text-align: right;">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                        
                        @if($order->diskon_amount > 0)
                        <tr>
                            <td style="color: #27ae60;">Diskon ({{ $order->diskon_code }})</td>
                            <td style="text-align: right; color: #27ae60;">- Rp {{ number_format($order->diskon_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif

                        {{-- Subtotal sebelum biaya --}}
                        @php
                            $subtotal = $order->orderItems->sum('subtotal');
                        @endphp
                        <tr>
                            <td style="color: #666; font-size: 13px;">Subtotal Produk</td>
                            <td style="text-align: right; color: #666; font-size: 13px;">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>

                        {{-- Biaya Admin --}}
                        @if(($order->fee_admin ?? 0) > 0)
                        <tr>
                            <td style="color: #666; font-size: 13px;">Biaya Admin</td>
                            <td style="text-align: right; color: #666; font-size: 13px;">Rp {{ number_format($order->fee_admin, 0, ',', '.') }}</td>
                        </tr>
                        @endif

                        {{-- Biaya Layanan --}}
                        @if(($order->fee_service ?? 0) > 0)
                        <tr>
                            <td style="color: #666; font-size: 13px;">Biaya Layanan</td>
                            <td style="text-align: right; color: #666; font-size: 13px;">Rp {{ number_format($order->fee_service, 0, ',', '.') }}</td>
                        </tr>
                        @endif

                        {{-- PPN 11% --}}
                        @if(($order->fee_tax ?? 0) > 0)
                        <tr>
                            <td style="color: #666; font-size: 13px;">PPN (11%)</td>
                            <td style="text-align: right; color: #666; font-size: 13px;">Rp {{ number_format($order->fee_tax, 0, ',', '.') }}</td>
                        </tr>
                        @endif

                        <tr class="total-row">
                            <td>TOTAL BAYAR</td>
                            <td style="text-align: right;">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                @if($hasPhysical)
                    <div style="background: #fff8e1; border: 1px solid #ffecb3; color: #856404; padding: 15px; margin-top: 30px; border-radius: 8px; font-size: 13px; display: flex; align-items: center;">
                        <span style="font-size: 20px; margin-right: 10px;">📦</span>
                        <div>
                            <strong>Info Pengiriman:</strong> Item fisik (Travel Kit) sedang diproses. Resi akan dikirim melalui WhatsApp ke nomor {{ $order->customer->phone }}.
                        </div>
                    </div>
                @endif
                
                <center>
                    <a href="{{ route('public.invoice', $order->order_id) }}" class="btn-check" style="color: #ffffff !important;">Lihat Invoice di Website</a>
                </center>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} TixKita Project.<br>
            Jl. Ketintang No. 1, Surabaya
        </div>
    </div>

</body>
</html>