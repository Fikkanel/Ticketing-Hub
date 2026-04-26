@extends('layouts.public')

@section('title', 'Verifikasi Email - TixKita')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Header --}}
                <div class="card-header bg-warning text-dark text-center py-4">
                    <h3 class="mb-0 fw-bold"><i class="fas fa-key me-2"></i> Verifikasi Email</h3>
                    <p class="mb-0 small">Masukkan kode OTP untuk menyelesaikan pendaftaran</p>
                </div>
                
                <div class="card-body p-4">
                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    {{-- Email Info --}}
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-3 p-3">
                            <i class="fas fa-envelope fa-2x text-primary-custom mb-2"></i>
                            <p class="mb-0 text-muted small">Kode OTP telah dikirim ke:</p>
                            <p class="mb-0 fw-bold text-dark">{{ $email }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('customer.verify-otp.process') }}">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        
                        <div class="mb-4">
                            <label for="otp" class="form-label fw-bold small text-muted text-center d-block">MASUKKAN KODE 6 DIGIT</label>
                            <input type="text" class="form-control form-control-lg text-center fw-bold" 
                                id="otp" name="otp" maxlength="6" pattern="\d{6}" 
                                placeholder="● ● ● ● ● ●" 
                                style="font-size: 2rem; letter-spacing: 1rem;" 
                                required autofocus>
                        </div>
                        
                        <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold py-3 rounded-3 shadow text-dark">
                            <i class="fas fa-check-circle me-2"></i> Verifikasi & Selesaikan Pendaftaran
                        </button>
                    </form>
                    
                    {{-- Resend OTP --}}
                    <div class="text-center mt-4">
                        <p class="text-muted small mb-2">Tidak menerima kode?</p>
                        <form method="POST" action="{{ route('customer.resend-otp') }}" class="d-inline">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <button type="submit" class="btn btn-link text-decoration-none p-0" id="resend-btn">
                                <i class="fas fa-redo me-1"></i> Kirim Ulang OTP
                            </button>
                        </form>
                        <p class="text-muted small mt-2">Kode berlaku selama <strong>10 menit</strong></p>
                    </div>
                    
                    <hr class="my-4">
                    
                    {{-- Back Links --}}
                    <div class="text-center">
                        <a href="{{ route('customer.register') }}" class="text-muted text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Pendaftaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-focus and auto-tab for OTP input
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
</script>
@endpush
@endsection
