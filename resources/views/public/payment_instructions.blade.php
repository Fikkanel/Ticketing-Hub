@extends('layouts.public')

@section('title', 'Selesaikan Pembayaran')

@section('content')
    <div class="row justify-content-center py-5">
        <div class="col-md-5">
            
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom text-center pt-4 pb-0">
                    <p class="text-muted small mb-1 text-uppercase fw-bold ls-1">Batas Waktu Pembayaran</p>
                    <h3 class="fw-bold text-danger mb-3" id="countdown-timer">23:59:59</h3>
                </div>
                
                <div class="card-body p-4 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-bold">Total Tagihan</span>
                        <span class="fs-4 fw-bold text-dark">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="bg-white p-3 rounded-3 border shadow-sm mb-4">
                        <div class="d-flex align-items-center mb-2">
                            @if($order->metode_pembayaran == 'Bank Transfer')
                                <i class="fas fa-university fa-lg me-2 text-primary-custom"></i>
                            @elseif($order->metode_pembayaran == 'E-Wallet')
                                <i class="fas fa-wallet fa-lg me-2 text-success"></i>
                            @else
                                <i class="fas fa-credit-card fa-lg me-2 text-warning"></i>
                            @endif
                            <span class="fw-bold">{{ $order->metode_pembayaran }}</span>
                        </div>
                        
                        @if($order->metode_pembayaran == 'Bank Transfer')
                            <p class="small text-muted mb-1">Nomor Virtual Account:</p>
                            <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded border">
                                <span class="fw-bold font-monospace fs-5 text-dark" id="va-number">8800 1234 5678 9000</span>
                                <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('va-number')">Salin</button>
                            </div>
                        @else
                        @if($order->midtrans_snap_token)
                             {{-- MIDTRANS PAYMENT BUTTON --}}
                             <div class="text-center py-4">
                                 <p class="mb-3 text-muted">Silakan selesaikan pembayaran Anda sekarang.</p>
                                 <button id="pay-button" class="btn btn-primary-custom btn-lg w-100 fw-bold shadow-sm">
                                     <i class="fas fa-credit-card me-2"></i> Bayar Sekarang
                                 </button>
                             </div>
                        @else
                            {{-- ERROR STATE: TOKEN MISSING --}}
                             <div class="alert alert-warning text-center">
                                 <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i>
                                 <p class="mb-0 fw-bold">Token Pembayaran Tidak Ditemukan</p>
                                 <small>Silakan hubungi admin atau coba buat pesanan ulang.</small>
                             </div>
                        @endif
                        @endif
                    </div>
                    
                    {{-- TOMBOL SAYA SUDAH MEMBAYAR DIHAPUS UTK MIDTRANS OTOMATIS --}}
                    <div class="text-center mt-3">
                        <a href="{{ route('public.invoice', $order->order_id) }}" class="text-decoration-none small text-muted">
                            <i class="fas fa-arrow-right me-1"></i> Cek Status Pesanan (Invoice)
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <small class="text-muted"><i class="fas fa-lock me-1"></i> Pembayaran Anda dijamin aman dan terenkripsi.</small>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
{{-- Midtrans Snap.js --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Midtrans Integration
        var payButton = document.getElementById('pay-button');
        var snapToken = "{{ $order->midtrans_snap_token }}";

        if (payButton && snapToken) {
            payButton.addEventListener('click', function () {
                window.snap.pay(snapToken, {
                    onSuccess: function(result){
                        // Redirect to Invoice on success
                        window.location.href = "{{ route('public.invoice', $order->order_id) }}";
                    },
                    onPending: function(result){
                        Swal.fire('Menunggu Pembayaran', 'Silakan selesaikan pembayaran Anda.', 'info');
                        // Optional: Reload to update status if pending logic exists
                    },
                    onError: function(result){
                        Swal.fire('Gagal', 'Pembayaran gagal. Silakan coba lagi.', 'error');
                    },
                    onClose: function(){
                        // Do nothing, user can click pay again
                    }
                })
            });
        }
    });

    // Script Sederhana Countdown & Copy
    function copyToClipboard(elementId) {
        // ... (Keep existing copy logic if needed for fallback) ...
        var element = document.getElementById(elementId);
        if(element) {
             var copyText = element.innerText;
             navigator.clipboard.writeText(copyText).then(function() {
                 Swal.fire('Tersalin!', 'Text berhasil disalin.', 'success');
             });
        }
    }

    // Countdown Timer Palsu 24 Jam
    // ... (Keep or Refine)
</script>
@endsection
