@extends('layouts.public')

@section('title', 'Pesanan Berhasil')

@section('content')
<style>
    /* === CSS KHUSUS CETAK (Tidak mempengaruhi tampilan di layar HP/Laptop) === */
    @media print {
        /* 1. Sembunyikan elemen Navigasi, Tombol, Footer Website, dan Icon Sukses */
        header, footer, nav, .navbar, .btn, .card-footer, .no-print, .fa-stack, .text-center.mb-4 {
            display: none !important;
        }

        /* 2. Reset Halaman Kertas */
        @page {
            margin: 0.5cm; /* Margin tipis agar muat banyak */
            size: auto;
        }
        
        body, html {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            font-family: Arial, sans-serif !important; /* Font standar dokumen */
        }

        /* 3. PERBAIKAN UTAMA: Paksa Layout Menjadi Lebar Penuh (A4) */
        .container, .row {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Override col-md-6 agar melebar di kertas */
        .col-md-6 {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        /* 4. Bersihkan Styling Kartu (Hilangkan shadow, radius, dan padding tebal) */
        .card {
            border: 1px solid #ddd !important; /* Beri border tipis agar rapi */
            box-shadow: none !important;
            border-radius: 0 !important;
            margin-top: 0 !important;
        }

        /* Hilangkan padding py-5 yang membuat jarak atas terlalu jauh */
        .py-5 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        .card-body {
            padding: 20px !important;
        }

        /* 5. Pastikan Background Warna & Gradient Tetap Tercetak */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Pastikan background abu-abu total harga tercetak */
        .bg-light {
            background-color: #f8f9fa !important;
            border: 1px solid #eee !important;
        }

        /* Hilangkan URL Link */
        a[href]:after {
            content: none !important;
        }
    }
</style>

    <div class="row justify-content-center py-5">
        <div class="col-md-6">
            
            {{-- STATUS PEMBAYARAN DINAMIS --}}
            <div class="text-center mb-4">
                @if($order->status == 'Paid')
                    <div class="mb-3">
                        <span class="fa-stack fa-3x text-success">
                            <i class="fas fa-circle fa-stack-2x"></i>
                            <i class="fas fa-check fa-stack-1x fa-inverse"></i>
                        </span>
                    </div>
                    <h2 class="fw-bold text-dark">Pembayaran Berhasil!</h2>
                    <p class="text-muted">Terima kasih, pesanan Anda telah lunas.</p>
                @elseif($order->status == 'Pending')
                    <div class="mb-3">
                        <span class="fa-stack fa-3x text-warning">
                            <i class="fas fa-circle fa-stack-2x"></i>
                            <i class="fas fa-clock fa-stack-1x fa-inverse"></i>
                        </span>
                    </div>
                    <h2 class="fw-bold text-dark">Menunggu Pembayaran</h2>
                    <p class="text-muted">Silakan selesaikan pembayaran Anda.</p>
                    
                    {{-- QR Code QRIS Payment --}}
                    @if($order->qris_url)
                        <div class="card border-0 mb-4 shadow-sm" style="border-left: 3px solid var(--primary-color) !important;">
                                <h5 class="mb-0"><i class="fas fa-qrcode me-2"></i> Scan QRIS untuk Membayar</h5>
                            </div>
                            <div class="card-body text-center p-4">
                                <img src="{{ $order->qris_url }}" alt="QRIS QR Code" class="img-fluid mx-auto d-block" style="max-width: 280px;">
                                <p class="text-muted small mt-3 mb-2">Scan dengan aplikasi pembayaran:</p>
                                <div class="d-flex justify-content-center gap-3 flex-wrap">
                                    <span class="badge bg-success px-3 py-2">GoPay</span>
                                    <span class="badge bg-primary px-3 py-2">OVO</span>
                                    <span class="badge bg-info px-3 py-2">DANA</span>
                                    <span class="badge px-3 py-2" style="background: #ee4d2d; color: white;">ShopeePay</span>
                                    <span class="badge bg-secondary px-3 py-2">M-Banking</span>
                                </div>
                                <div class="alert alert-warning mt-3 mb-0 text-start">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Penting:</strong> Selesaikan pembayaran dalam waktu 15 menit.
                                </div>
                            </div>
                        </div>
                    @elseif(isset($snapToken) && $snapToken)
                        <button id="pay-button" class="btn btn-primary-custom btn-lg shadow-sm rounded-pill px-5 mt-2">
                            <i class="fas fa-credit-card me-2"></i> Bayar Sekarang
                        </button>
                    @else
                         <div class="alert alert-info d-inline-block mt-2">
                            Silakan lakukan pembayaran sesuai instruksi yang diberikan sebelumnya.
                         </div>
                    @endif

                @elseif($order->status == 'Cancelled' || $order->status == 'Expired')
                    <div class="mb-3">
                        <span class="fa-stack fa-3x text-danger">
                            <i class="fas fa-circle fa-stack-2x"></i>
                            <i class="fas fa-times fa-stack-1x fa-inverse"></i>
                        </span>
                    </div>
                    <h2 class="fw-bold text-dark">Pesanan Dibatalkan</h2>
                    <p class="text-muted">Maaf, pesanan ini telah dibatalkan atau kadaluarsa.</p>
                @endif
            </div>

            {{-- Kartu Tiket / Invoice --}}
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-relative">
                {{-- Hiasan Garis Atas --}}
                <div class="position-absolute top-0 start-0 w-100" style="height: 6px; background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));"></div>
                
                <div class="card-body p-4">
                    {{-- Header Invoice --}}
                    <div class="d-flex justify-content-between align-items-start mb-4 border-bottom pb-3">
                        <div>
                            <p class="small text-muted mb-1 text-uppercase fw-bold">ID Pesanan</p>
                            <h5 class="fw-bold font-monospace text-primary-custom">#{{ $order->uuid ?? $order->order_id }}</h5>
                        </div>
                        <div class="text-end">
                            <p class="small text-muted mb-1 text-uppercase fw-bold">Tanggal</p>
                            <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</p>
                        </div>
                    </div>

                    {{-- Detail Item --}}
                    <div class="mb-4">
                        <p class="small text-muted text-uppercase fw-bold mb-2">Rincian Produk</p>
                        @foreach ($order->orderItems as $item)
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-light">
                                <div>
                                    <span class="fw-semibold text-dark">{{ $item->product->nama_produk ?? 'Produk Dihapus' }}</span>
                                    <span class="text-muted small ms-1">x{{ $item->kuantitas }}</span>
                                </div>
                                <span class="fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Total --}}
                    <div class="bg-light p-3 rounded-3">
                        @if ($order->diskon_amount > 0)
                            <div class="d-flex justify-content-between text-success small mb-1">
                                <span>Diskon ({{ $order->diskon_code }})</span>
                                <span>-Rp {{ number_format($order->diskon_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        {{-- FEES --}}
                        @if ($order->fee_admin > 0)
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>Biaya Admin</span>
                                <span>Rp {{ number_format($order->fee_admin, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if ($order->fee_service > 0)
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>Biaya Layanan</span>
                                <span>Rp {{ number_format($order->fee_service, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if ($order->fee_tax > 0)
                            <div class="d-flex justify-content-between text-muted small mb-1">
                                <span>PPN (11%)</span>
                                <span>Rp {{ number_format($order->fee_tax, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="border-top my-2"></div>
                        <div class="d-flex justify-content-between fw-bold fs-5 text-dark">
                            <span>Total Bayar</span>
                            <span>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- TOMBOL KONFIRMASI WHATSAPP (Baru) --}}
                    {{-- TOMBOL KONFIRMASI WHATSAPP (Baru) --}}
                    {{-- HIDDEN AS PER REQUEST (FEATURE PENDING)
                    @if(isset($adminWhatsapp) && $adminWhatsapp)
                        @php
                            $waMessage = "Halo admin, saya sudah melakukan pembayaran untuk Order ID: {$order->order_id}. Berikut saya lampirkan bukti pembayarannya.";
                            $waLink = "https://wa.me/{$adminWhatsapp}?text=" . urlencode($waMessage);
                        @endphp
                        <div class="mt-4 mb-2">
                            <a href="{{ $waLink }}" target="_blank" class="btn btn-success w-100 py-3 fw-bold shadow-sm">
                                <i class="fab fa-whatsapp me-2"></i> Konfirmasi ke WhatsApp
                            </a>
                            <small class="text-muted d-block mt-2 text-center">Wajib melampirkan screenshot bukti pembayaran untuk verifikasi.</small>
                        </div>
                    @endif
                    --}}

                    {{-- TOMBOL GABUNG WHATSAPP UNTUK SEMINAR --}}
                    @php
                        // Check if any item is from a seminar event (has seminar_whatsapp_link)
                        $seminarLink = null;
                        foreach($order->orderItems as $item) {
                            if ($item->product && $item->product->event && $item->product->event->seminar_whatsapp_link) {
                                $seminarLink = $item->product->event->seminar_whatsapp_link;
                                break;
                            }
                        }
                    @endphp

                    @if($order->status == 'Paid' && $seminarLink)
                        {{-- SEMINAR MODE: Show WhatsApp Join Button --}}
                        <div class="mt-4">
                            <a href="{{ $seminarLink }}" target="_blank" class="btn btn-success w-100 py-3 fw-bold shadow-sm" style="background: #25D366; border-color: #25D366;">
                                <i class="fab fa-whatsapp me-2 fa-lg"></i> Gabung Grup WhatsApp
                            </a>
                            <div class="text-center mt-2 small text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Klik tombol di atas untuk bergabung ke grup seminar.
                            </div>
                        </div>
                    @else
                        {{-- REGULAR MODE: Show email confirmation notice --}}
                        <div class="mt-4 text-center small text-muted">
                            <p class="mb-1"><i class="fas fa-envelope me-1"></i> Bukti pembayaran telah dikirim ke:</p>
                            <strong>{{ $order->customer->email ?? 'Email Anda' }}</strong>
                        </div>
                    @endif
                </div>
                
                {{-- Footer Tombol (Hilang saat diprint) --}}
                <div class="card-footer bg-white p-3 border-top-0 text-center no-print">
                    <a href="{{ route('public.index') }}" class="btn btn-primary-custom w-100 mb-2 py-2">
                        <i class="fas fa-calendar-alt me-2"></i> Cari Event Lainnya
                    </a>
                    <button onclick="window.print()" class="btn btn-link text-decoration-none text-muted btn-sm">
                        <i class="fas fa-print me-1"></i> Cetak Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@if(isset($snapToken) && $snapToken)
    <script type="text/javascript"
            src="https://app.{{ env('MIDTRANS_IS_PRODUCTION', false) ? '' : 'sandbox.' }}midtrans.com/snap/snap.js"
            data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
    <script type="text/javascript">
      var payButton = document.getElementById('pay-button');
      if(payButton) {
          payButton.addEventListener('click', function () {
            window.snap.pay('{{ $snapToken }}', {
              onSuccess: function(result){
                alert("Pembayaran Berhasil!");
                window.location.reload();
              },
              onPending: function(result){
                alert("Menunggu pembayaran...");
                window.location.reload();
              },
              onError: function(result){
                alert("Pembayaran gagal!");
                window.location.reload();
              },
              onClose: function(){
                // Do nothing, stay on invoice
              }
            });
          });
      }
    </script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof localStorage !== 'undefined' && localStorage.getItem('TixKita_cart')) {
            localStorage.removeItem('TixKita_cart');
        }
        if (typeof updateCartIcon === 'function') {
            updateCartIcon(); 
        }
    });
</script>
@endsection