<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>E-Ticket: {{ $order->order_id }}</title>
    @php
        // Get all site settings for branding
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        
        $siteName = $settings['site_title'] ?? config('app.name', 'TixKita');
        $siteUrl = config('app.url', 'tixkita.id');
        $logoPath = $settings['logo_path'] ?? null;
        
        // Colors from settings
        $headerBgColor = $settings['header_bg_color'] ?? '#1a365d';
        $headerTextColor = $settings['header_text_color'] ?? '#ffffff';
        $footerBgColor = $settings['footer_bg_color'] ?? '#1a365d';
        $footerTextColor = $settings['footer_text_color'] ?? '#ffffff';
        
        // Social media links
        $socialInstagram = $settings['social_instagram'] ?? null;
        $socialTwitter = $settings['social_twitter'] ?? null;
        $socialFacebook = $settings['social_facebook'] ?? null;
        $socialLinkedin = $settings['social_linkedin'] ?? null;
        
        // Footer links
        $contactLink = $settings['footer_contact_link'] ?? null;
        
        // Get logo as base64 for DomPDF
        $logoContent = null;
        if ($logoPath) {
            $logoFullPath = public_path('storage/' . $logoPath);
            if (file_exists($logoFullPath)) {
                $logoData = @file_get_contents($logoFullPath);
                if ($logoData) {
                    $logoMime = mime_content_type($logoFullPath);
                    $logoContent = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
                }
            }
        }
    @endphp
    <style>
        @page { margin: 0; }
        body { 
            margin: 0; 
            font-family: 'Helvetica', sans-serif; 
            background-color: #f5f5f5; 
            color: #333;
        }

        .page-container {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            background: #fff;
        }

        /* Header - Dynamic Color */
        .ticket-header {
            background-color: {{ $headerBgColor }};
            padding: 12px 25px;
            color: {{ $headerTextColor }};
        }
        .ticket-header table {
            width: 100%;
        }
        .header-logo-img {
            height: 30px;
            width: auto;
            vertical-align: middle;
            margin-right: 8px;
        }
        .header-logo {
            font-size: 18px;
            font-weight: bold;
            color: {{ $headerTextColor }};
            letter-spacing: 1px;
            vertical-align: middle;
        }
        .header-label {
            font-size: 11px;
            color: {{ $headerTextColor }};
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Ticket Content Area */
        .ticket-wrapper {
            padding: 25px;
            background: #fff;
        }

        /* Banner Event */
        .ticket-banner {
            width: 100%;
            height: 160px;
            background-color: #eee;
            text-align: center;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .ticket-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Header Info */
        .event-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #1a1a1a;
        }
        .event-category {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        /* Grid Informasi */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-label {
            font-size: 9px;
            color: #999;
            text-transform: uppercase;
            padding-bottom: 3px;
        }
        .info-value {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            padding-bottom: 12px;
        }

        /* Pemisah Tiket */
        .ticket-divider {
            border-top: 2px dashed #e0e0e0;
            margin: 15px 0;
            position: relative;
        }

        /* Bagian QR */
        .qr-box {
            text-align: center;
            padding: 5px;
        }
        .qr-img {
            width: 90px;
            height: 90px;
        }
        .ticket-id {
            display: block;
            font-family: monospace;
            font-size: 10px;
            margin-top: 5px;
            color: #555;
            letter-spacing: 1px;
        }

        .footer-text {
            font-size: 9px;
            color: #888;
            line-height: 1.5;
        }

        .price-box {
            background: #f0f7ff;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: left;
            border: 1px solid #d0e3ff;
        }
        .price-value {
            font-size: 16px;
            font-weight: bold;
            color: #000000;
        }

        /* Footer - Dynamic Color */
        .ticket-footer {
            background-color: {{ $footerBgColor }};
            padding: 12px 25px;
            color: {{ $footerTextColor }};
        }
        .ticket-footer table {
            width: 100%;
        }
        .footer-logo-img {
            height: 20px;
            width: auto;
            vertical-align: middle;
            margin-right: 5px;
        }
        .footer-logo {
            font-size: 12px;
            font-weight: bold;
            color: {{ $footerTextColor }};
            vertical-align: middle;
        }
        .footer-contact {
            font-size: 9px;
            color: {{ $footerTextColor }};
            opacity: 0.8;
        }
        .footer-contact a {
            color: {{ $footerTextColor }};
            text-decoration: none;
        }
    </style>
</head>
<body>
    @foreach($order->orderItems as $item)
        @if($item->product && $item->product->tipe == 'Digital')
            @for($i = 0; $i < $item->kuantitas; $i++)
                @php
                    $uniqueCode = "TIKET-" . $item->item_id . "-" . ($i+1);
                    
                    // Generate QR code using SimpleSoftwareIO library (local generation)
                    $qrContent = null;
                    
                    try {
                        // Use SimpleSoftwareIO QR Code library
                        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                            ->size(150)
                            ->margin(1)
                            ->generate($uniqueCode);
                        
                        if ($qrCode) {
                            $qrContent = 'data:image/png;base64,' . base64_encode($qrCode);
                        }
                    } catch (\Exception $e) {
                        // Fallback: Try SVG format (more compatible)
                        try {
                            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                                ->size(150)
                                ->generate($uniqueCode);
                            
                            if ($qrSvg) {
                                $qrContent = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
                            }
                        } catch (\Exception $e2) {
                            $qrContent = null;
                        }
                    }
                    
                    // Ultimate fallback: Use PHP GD to create placeholder with code
                    if (!$qrContent) {
                        if (function_exists('imagecreate')) {
                            $img = imagecreate(90, 90);
                            $bg = imagecolorallocate($img, 255, 255, 255);
                            $textColor = imagecolorallocate($img, 50, 50, 50);
                            $borderColor = imagecolorallocate($img, 200, 200, 200);
                            imagerectangle($img, 0, 0, 89, 89, $borderColor);
                            imagestring($img, 2, 10, 35, $order->order_id, $textColor);
                            imagestring($img, 1, 15, 50, "Ticket " . ($i+1), $textColor);
                            ob_start();
                            imagepng($img);
                            $imgData = ob_get_clean();
                            imagedestroy($img);
                            $qrContent = 'data:image/png;base64,' . base64_encode($imgData);
                        }
                    }

                    // Ticket count info
                    $ticketNumber = $i + 1;
                    $totalTickets = $item->kuantitas;
                @endphp

                <div class="page-container">
                    {{-- HEADER --}}
                    <div class="ticket-header">
                        <table>
                            <tr>
                                <td align="left">
                                    @if($logoContent)
                                        <img src="{{ $logoContent }}" class="header-logo-img" alt="Logo">
                                    @else
                                        <span class="header-logo">{{ $siteName }}</span>
                                    @endif
                                </td>
                                <td align="right">
                                    <span class="header-label">E-Ticket</span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    {{-- MAIN CONTENT --}}
                    <div class="ticket-wrapper">
                        {{-- Banner Event --}}
                        <div class="ticket-banner">
                            @if($item->product->event->banner_image)
                                <img src="{{ public_path('storage/' . $item->product->event->banner_image) }}" alt="Banner">
                            @else
                                <div style="padding-top: 70px; color: #ccc;">No Event Image</div>
                            @endif
                        </div>

                        {{-- Event Title --}}
                        <div class="event-title">{{ $item->product->event->judul }}</div>
                        <div class="event-category">{{ $item->product->nama_produk }} &nbsp;•&nbsp; TICKET {{ $ticketNumber }} of {{ $totalTickets }}</div>

                        {{-- Info Grid --}}
                        <table class="info-grid">
                            <tr>
                                {{-- Kolom Kiri: Info Detail --}}
                                <td width="50%" style="vertical-align: top;">
                                    <div class="info-label">Nama / Name</div>
                                    <div class="info-value">{{ $order->customer->name }}</div>
                                    
                                    <div class="info-label">Tanggal Acara / Event Date</div>
                                    <div class="info-value">
                                        {{ \Carbon\Carbon::parse($item->product->event->tgl_mulai)->isoFormat('dddd, D MMM Y') }}<br>
                                        {{ \Carbon\Carbon::parse($item->product->event->tgl_mulai)->format('H:i') }} WIB
                                    </div>

                                    <div class="info-label">Lokasi / Venue</div>
                                    <div class="info-value">{{ $item->product->event->location->nama_lokasi }}</div>
                                </td>

                                {{-- Kolom Tengah: Nomor Pesanan & Harga --}}
                                <td width="25%" style="vertical-align: top;">
                                    <div class="info-label">Kode Pesanan / Order Code</div>
                                    <div class="info-value">#{{ $order->order_id }}</div>

                                    <div class="price-box">
                                        <div class="info-label">Harga / Price</div>
                                        <div class="price-value">Rp {{ number_format($item->product->harga, 0, ',', '.') }}</div>
                                    </div>
                                </td>

                                {{-- Kolom Kanan: QR Code --}}
                                <td width="25%" class="qr-box" style="vertical-align: top;">
                                    @if($qrContent)
                                        <img src="{{ $qrContent }}" class="qr-img" alt="QR Code">
                                    @else
                                        <div style="width: 90px; height: 90px; border: 1px solid #ddd; text-align: center; padding-top: 35px; font-size: 9px; color: #999;">QR Error</div>
                                    @endif
                                    <span class="ticket-id">{{ $item->item_id }}-{{ $i+1 }}</span>
                                </td>
                            </tr>
                        </table>

                        <div class="ticket-divider"></div>

                        {{-- Informasi Penting --}}
                        <div style="text-align: left;">
                            <div style="font-weight: bold; font-size: 11px; margin-bottom: 6px; color: #333;">Syarat dan Ketentuan / Terms & Conditions</div>
                            <div class="footer-text">
                                • Harap tunjukkan QR Code ini kepada petugas di lokasi acara.<br>
                                • Tiket ini berlaku untuk 1 (satu) orang sesuai kategori produk.<br>
                                • Dilarang menggandakan atau menyebarluaskan file tiket ini.<br>
                                • Tiket yang sudah dibeli tidak dapat dikembalikan atau ditukar.
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="ticket-footer">
                        <table>
                            <tr>
                                <td align="left" width="30%">
                                    @if($logoContent)
                                        <img src="{{ $logoContent }}" class="footer-logo-img" alt="Logo">
                                    @else
                                        <span class="footer-logo">{{ $siteName }}</span>
                                    @endif
                                </td>
                                <td align="right" width="70%">
                                    <span class="footer-contact">
                                        @php
                                            // Extract Instagram username
                                            $igUsername = null;
                                            if ($socialInstagram) {
                                                $igUsername = preg_replace('/^https?:\/\/(www\.)?instagram\.com\//', '@', rtrim($socialInstagram, '/'));
                                            }
                                            
                                            // Extract WhatsApp number from wa.me link
                                            $waNumber = null;
                                            if ($contactLink && str_contains($contactLink, 'wa.me')) {
                                                $waNumber = preg_replace('/^https?:\/\/wa\.me\//', '+', $contactLink);
                                            }
                                        @endphp
                                        
                                        @if($igUsername)
                                            {{ $igUsername }} &nbsp;
                                        @endif
                                        @if($socialFacebook)
                                            | Facebook &nbsp;
                                        @endif
                                        @if($waNumber)
                                            | WA: {{ $waNumber }}
                                        @elseif($contactLink)
                                            | {{ str_replace(['http://', 'https://'], '', $siteUrl) }}
                                        @else
                                            | {{ str_replace(['http://', 'https://'], '', $siteUrl) }}
                                        @endif
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if(!$loop->last || $i < $item->kuantitas - 1)
                    <div style="page-break-after: always;"></div>
                @endif
            @endfor
        @endif
    @endforeach
</body>
</html>