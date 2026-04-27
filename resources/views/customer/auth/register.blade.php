@extends('layouts.public')

@section('title', 'Daftar - TixKita')

@section('content')
<style>
    /* Auth Page Overrides */
    body { background: #ffffff !important; }
    .navbar { background: var(--primary-color) !important; box-shadow: none !important; }
    .navbar .nav-link { color: #fff !important; }
    .navbar .hamburger-icon span { background: #fff !important; }
    .navbar .fas, .navbar .far, .navbar .fab { color: #fff !important; }
    .navbar-brand { color: #fff !important; }
    main { padding: 0 !important; }
    footer { display: none !important; }

    .auth-container {
        max-width: 400px;
        margin: 0 auto;
        padding: 40px 24px 60px;
    }
    .auth-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: #1a1a1a;
        text-align: center;
        margin-bottom: 8px;
    }
    .auth-subtitle {
        font-size: 0.95rem;
        color: #666;
        text-align: center;
        margin-bottom: 36px;
    }
    .auth-input-wrapper {
        border: 1.5px solid #e0e0e0;
        border-radius: 14px;
        padding: 15px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
        background: #fff;
        transition: border-color 0.2s;
    }
    .auth-input-wrapper:focus-within {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(58, 125, 68, 0.1);
    }
    .auth-input-wrapper .auth-icon {
        color: #888;
        font-size: 1.15rem;
        min-width: 24px;
        text-align: center;
    }
    .auth-input-wrapper input {
        border: none;
        outline: none;
        background: transparent;
        font-size: 1rem;
        color: #333;
        width: 100%;
        padding: 0;
    }
    .auth-input-wrapper input::placeholder {
        color: #999;
    }
    .auth-btn {
        width: 100%;
        background: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 14px;
        padding: 16px;
        font-size: 1.2rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s;
        display: block;
        text-align: center;
        margin-top: 24px;
    }
    .auth-btn:hover {
        background: var(--secondary-color);
        color: #fff;
    }
    .auth-login-link {
        text-align: center;
        margin-top: 28px;
    }
    .auth-login-link span {
        color: #555;
        font-size: 0.95rem;
    }
    .auth-login-link a {
        color: var(--primary-color);
        text-decoration: underline;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .auth-terms {
        text-align: center;
        margin-top: 16px;
        font-size: 0.85rem;
        color: #888;
    }
    .auth-terms a {
        color: var(--primary-color);
        text-decoration: underline;
    }
</style>

<div class="auth-container">
    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="auth-title">Daftar Akun</h1>
    <p class="auth-subtitle">Silakan isi form dibawah</p>

    <form method="POST" action="{{ route('customer.register.process') }}">
        @csrf
        
        <div class="auth-input-wrapper">
            <span class="auth-icon"><i class="fas fa-user"></i></span>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama Anda" required autofocus
                   oninput="this.value = this.value.toUpperCase()">
        </div>
        
        <div class="auth-input-wrapper">
            <span class="auth-icon"><i class="fas fa-phone"></i></span>
            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="Nomor telepon">
        </div>
        
        <div class="auth-input-wrapper">
            <span class="auth-icon"><i class="far fa-envelope"></i></span>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Alamat Email" required>
        </div>
        
        <div class="auth-input-wrapper">
            <span class="auth-icon"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="password" placeholder="Password" required minlength="6">
            <span class="auth-icon toggle-password" style="cursor: pointer;" onclick="togglePassword('password', this)"><i class="far fa-eye"></i></span>
        </div>
        
        <div class="auth-input-wrapper">
            <span class="auth-icon"><i class="fas fa-lock"></i></span>
            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Ulangi password" required minlength="6">
            <span class="auth-icon toggle-password" style="cursor: pointer;" onclick="togglePassword('password_confirmation', this)"><i class="far fa-eye"></i></span>
        </div>
        
        <div class="mb-3">
            <div class="d-flex align-items-center mb-2 justify-content-between">
                <div>{!! captcha_img('flat') !!}</div>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="reloadCaptcha()"><i class="fas fa-sync-alt"></i> Reload</button>
            </div>
            <div class="auth-input-wrapper mb-0">
                <span class="auth-icon"><i class="fas fa-shield-alt"></i></span>
                <input type="text" name="captcha" placeholder="Masukkan kode di atas" required>
            </div>
        </div>

        <button type="submit" class="auth-btn mt-3">Daftar</button>
    </form>
    
    <script>
        function reloadCaptcha() {
            var img = document.querySelector('img[src*="captcha"]');
            if(img) {
                var currentSrc = img.src.split('?')[0];
                img.src = currentSrc + '?' + Math.random();
            }
        }
        
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            const icon = iconElement.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
    
    <div class="auth-terms">
        Dengan mendaftar, Anda menyetujui 
        <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Syarat & Ketentuan</a> TixKita.
    </div>
    
    <div class="auth-login-link">
        <span>Sudah punya akun?</span> <a href="{{ route('customer.login') }}">Masuk sekarang</a>
    </div>
</div>

<!-- Modal Syarat & Ketentuan -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold" id="termsModalLabel">Syarat & Ketentuan TixKita</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-4 py-4 text-start" style="color: #4a5568;">
        
        <div class="mb-4">
            <h6 class="fw-bold text-dark">1. Ketentuan Penggunaan</h6>
            <p class="small mb-0">TixKita ditawarkan kepada Anda dengan syarat Anda menerima syarat, ketentuan, dan pemberitahuan yang terkandung di sini.</p>
        </div>

        <div class="mb-4">
            <h6 class="fw-bold text-dark">2. Pendaftaran dan Akun</h6>
            <p class="small mb-0">Anda bertanggung jawab penuh atas kerahasiaan informasi akun Anda serta segala aktivitas yang terjadi di bawah akun Anda.</p>
        </div>

        <div class="mb-4">
            <h6 class="fw-bold text-dark">3. Pembelian dan Refund</h6>
            <ul class="small mb-0 ps-3">
                <li>Tiket event yang sudah dibeli <strong>tidak dapat dikembalikan</strong>, kecuali jika event dibatalkan oleh penyelenggara.</li>
                <li>Keputusan pengembalian dana sepenuhnya merupakan hak prerogatif TixKita.</li>
            </ul>
        </div>

        <div class="mb-4">
            <h6 class="fw-bold text-dark">4. Keamanan & Privasi</h6>
            <p class="small mb-0">Informasi Anda aman bersama kami. Kami hanya menggunakan informasi pribadi Anda untuk menyelesaikan pesanan Anda dan tidak akan menyalahgunakan atau menjualnya kepada pihak lain.</p>
        </div>

        <div class="alert alert-light border-start border-4 border-primary mt-4 mb-0 small">
            Untuk membaca syarat dan ketentuan secara lengkap, silakan kunjungi halaman <a href="{{ route('public.terms') }}" target="_blank" class="fw-bold text-decoration-none">Syarat & Ketentuan</a> kami.
        </div>

      </div>
      <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-primary-custom w-100 rounded-3" data-bs-dismiss="modal">Saya Mengerti & Setuju</button>
      </div>
    </div>
  </div>
</div>
@endsection
