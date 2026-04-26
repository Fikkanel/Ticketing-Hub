@extends('layouts.public')

@section('title', 'Masuk - TixKita')

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
    .auth-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        margin-top: 4px;
    }
    .auth-row .form-check-input {
        border-color: #ccc;
        width: 18px;
        height: 18px;
    }
    .auth-row .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .auth-row .form-check-label {
        font-size: 0.9rem;
        color: #555;
        margin-left: 4px;
    }
    .auth-row a {
        color: var(--primary-color);
        font-size: 0.9rem;
        text-decoration: underline;
        font-weight: 500;
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
    }
    .auth-btn:hover {
        background: var(--secondary-color);
        color: #fff;
    }
    .auth-organizer-link {
        text-align: center;
        margin-top: 24px;
    }
    .auth-organizer-link a {
        color: var(--primary-color);
        font-size: 0.9rem;
        text-decoration: underline;
    }
    .auth-register-link {
        text-align: center;
        margin-top: 28px;
    }
    .auth-register-link span {
        color: #555;
        font-size: 0.95rem;
    }
    .auth-register-link a {
        color: var(--primary-color);
        text-decoration: underline;
        font-weight: 600;
        font-size: 0.95rem;
    }
</style>

<div class="auth-container">
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
    
    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li class="small">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h1 class="auth-title">Masuk</h1>
    <p class="auth-subtitle">Gunakan akun Anda untuk masuk</p>

    <form method="POST" action="{{ route('customer.login.process') }}">
        @csrf
        
        <div class="auth-input-wrapper">
            <span class="auth-icon"><i class="far fa-envelope"></i></span>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Alamat Email" required autofocus>
        </div>
        
        <div class="auth-input-wrapper">
            <span class="auth-icon"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" placeholder="Password" required>
        </div>
        
        <div class="auth-row">
            <div class="form-check mb-0">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <a href="{{ route('customer.password.request') }}">Lupa Password?</a>
        </div>
        
        <button type="submit" class="auth-btn">Masuk</button>
    </form>
    
    <div class="auth-organizer-link">
        <a href="{{ route('admin.login') }}">Login sebagai Organizer</a>
    </div>
    
    <div class="auth-register-link">
        <span>Belum punya akun?</span> <a href="{{ route('customer.register') }}">Daftar sekarang</a>
    </div>
</div>
@endsection
