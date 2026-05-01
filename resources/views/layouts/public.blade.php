<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $globalSettings['site_title'] ?? 'TixKita' }} - @yield('title', 'Jelajahi Serunya')</title>
    @if(isset($globalSettings['favicon_path']) && $globalSettings['favicon_path'])
        {{-- Favicon untuk browser dan Google Search --}}
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $globalSettings['favicon_path']) }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('storage/' . $globalSettings['favicon_path']) }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('storage/' . $globalSettings['favicon_path']) }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('storage/' . $globalSettings['favicon_path']) }}">
    @else
        {{-- Fallback ke favicon.ico default --}}
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @php
        $primaryColor = $globalSettings['primary_color'] ?? '#3a7d44';
        $secondaryColor = $globalSettings['secondary_color'] ?? '#2e6636';
        
        // New Settings
        $headerBg = $globalSettings['header_bg_color'] ?? '#ffffff';
        $headerText = $globalSettings['header_text_color'] ?? '#000000';
        $footerBg = $globalSettings['footer_bg_color'] ?? '#ffffff';
        $footerText = $globalSettings['footer_text_color'] ?? '#333333';
    @endphp

    <style>
        :root {
            --primary-color: {{ $primaryColor }};
            --secondary-color: {{ $secondaryColor }};
            --header-bg: {{ $headerBg }};
            --header-text: {{ $headerText }};
            --footer-bg: {{ $footerBg }};
            --footer-text: {{ $footerText }};
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }

        /* Utility Classes for Dynamic Colors */
        .text-primary { color: var(--primary-color) !important; }
        .text-secondary { color: var(--secondary-color) !important; }
        .bg-primary { background-color: var(--primary-color) !important; }
        .bg-secondary { background-color: var(--secondary-color) !important; }
        
        .btn-primary, .btn-primary-custom {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: white !important;
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active, 
        .btn-primary-custom:hover, .btn-primary-custom:focus, .btn-primary-custom:active {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
            color: white !important;
        }

        .btn-outline-primary, .btn-outline-primary-custom {
            color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            background-color: transparent !important;
        }
        .btn-outline-primary:hover, .btn-outline-primary-custom:hover {
            background-color: var(--primary-color) !important;
            color: white !important;
        }

        /* Top Bar Yesplis Style */
        .top-bar {
            background-color: var(--primary-color);
            color: #ffffff;
            font-size: 0.85rem;
            padding: 8px 0;
        }
        .top-bar a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        .top-bar a:hover {
            opacity: 0.8;
        }

        /* Navbar Yesplis Style */
        .navbar {
            background-color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 12px 0;
            z-index: 1030;
        }
        .navbar .dropdown-menu {
            z-index: 1050;
        }
        .navbar-brand {
            font-weight: 800;
            color: var(--primary-color) !important;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            letter-spacing: -0.5px;
        }
        .navbar .nav-link {
            color: #333333 !important;
            font-weight: 600;
        }
        
        /* Search Bar Yesplis Style */
        .search-container {
            flex-grow: 1;
            max-width: 650px;
            margin: 0 2rem;
        }
        .search-container .input-group {
            box-shadow: 0 0 0 1px #dee2e6;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
        }
        .search-container .form-control {
            border: none;
            padding: 0.7rem 1.2rem;
            font-size: 0.95rem;
            background-color: #ffffff;
            color: #333;
        }
        .search-container .form-control:focus {
            box-shadow: none;
        }
        .search-container .form-control::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }
        .search-container .btn-search {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0 1.5rem;
            transition: background 0.2s;
        }
        .search-container .btn-search:hover {
            background-color: var(--secondary-color);
        }
        
        @media (max-width: 991.98px) {
            .search-container {
                margin: 1rem 0;
                width: 100%;
                max-width: 100%;
            }
            .top-bar {
                display: none !important;
            }
        }

        /* Card Event Style (Mirip Loket/Pockets) */
        .card-event {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
            height: 100%;
        }
        .card-event:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.1);
        }
        .card-event img {
            height: auto;
            object-fit: cover;
            width: 100%;
        }
        .card-event .card-body {
            padding: 1.25rem;
        }
        .event-date {
            font-size: 0.85rem;
            color: var(--secondary-color); /* Ubah ke Secondary */
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }
        .event-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            color: #111;
        }
        .event-location {
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* Buttons - Existing Override */
        /* Hapus .btn-primary-custom lama agar tidak duplikat, sudah didefinisikan di atas */

        /* Footer */
        footer {
            background: var(--footer-bg);
            color: var(--footer-text);
            margin-top: 50px;
            border-top: 1px solid #eee;
        }
        footer .text-muted {
            color: var(--footer-text) !important;
            opacity: 0.8;
        }
        footer a {
            color: var(--footer-text) !important;
        }

        /* Hamburger Animation */
        .hamburger-icon {
            width: 24px;
            height: 20px;
            position: relative;
            transform: rotate(0deg);
            transition: .5s ease-in-out;
            cursor: pointer;
        }

        .hamburger-icon span {
            display: block;
            position: absolute;
            height: 2px;
            width: 100%;
            background: var(--header-text);
            border-radius: 9px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }

        .hamburger-icon span:nth-child(1) { top: 0px; }
        .hamburger-icon span:nth-child(2) { top: 9px; }
        .hamburger-icon span:nth-child(3) { top: 18px; }

        /* Transform to X when open (Bootstrap removes .collapsed class) */
        .navbar-toggler:not(.collapsed) .hamburger-icon span:nth-child(1) {
            top: 9px;
            transform: rotate(135deg);
        }
        .navbar-toggler:not(.collapsed) .hamburger-icon span:nth-child(2) {
            opacity: 0;
            left: -60px;
        }
        .navbar-toggler:not(.collapsed) .hamburger-icon span:nth-child(3) {
            top: 9px;
            transform: rotate(-135deg);
        }

        /* Responsive Banner Height */
        .hero-banner-item {
            position: relative;
            width: 100%;
            overflow: hidden;
        }
        
        /* Mobile: Aspect Ratio Based on Image (1300x500 = 2.6) */
        @media (max-width: 767.98px) {
            .hero-banner-item {
                width: 100%;
                aspect-ratio: 13/5; /* Menjaga rasio 1300x500 */
            }
            .hero-banner-item img {
                height: 100% !important;
                width: 100%;
                object-fit: cover; /* Memenuhi kotak rasio */
            }
        }

        /* Desktop: Fixed Height (1300x300 px Aspect Ratio) */
        @media (min-width: 768px) {
            .hero-banner-item {
                /* height: 300px;  REMOVED fixed height */
                width: 100%;
                aspect-ratio: 13/3; /* 1300x300 ratio */
                height: auto !important;
            }
            .hero-banner-item img {
                height: 100% !important;
                width: 100%;
                object-fit: cover; 
            }
        }

        /* Pockets.id Style Mobile Menu — Smooth Slide Animation */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                background-color: var(--header-bg);
                position: absolute;
                top: 100%; 
                left: 0;
                right: 0;
                padding: 0 1.5rem;
                border-top: 1px solid rgba(0,0,0,0.05);
                z-index: 9999;
                box-shadow: 0 15px 30px rgba(0,0,0,0.1); 
                border-radius: 0 0 16px 16px;
                display: block !important;
                overflow: hidden;
                max-height: 0;
                opacity: 0;
                transition: max-height 0.25s ease-out, opacity 0.2s ease-out, padding 0.25s ease-out;
                pointer-events: none;
            }
            .navbar-collapse.collapsing {
                max-height: 0 !important;
                opacity: 0;
                transition: max-height 0.2s ease-out, opacity 0.15s ease-out, padding 0.2s ease-out;
            }
            .navbar-collapse.collapse.show {
                max-height: 85vh;
                opacity: 1;
                padding: 1.5rem;
                overflow-y: auto;
                pointer-events: auto;
            }
            .navbar-nav {
                margin-top: 1rem;
            }
            .navbar-nav .nav-item {
                border-bottom: 1px solid rgba(0,0,0,0.05);
            }
            .navbar-nav .nav-link {
                color: var(--header-text) !important;
                font-size: 1.1rem;
                font-weight: 600;
                padding: 1rem 0;
            }
            /* Search Bar in Menu */
            .navbar-collapse form {
                width: 100% !important;
            }
            .navbar-collapse .input-group {
                box-shadow: none;
                background: #fff;
                border-radius: 8px;
                overflow: hidden;
            }
        }

        /* Hide Google Translate Top Banner */
        body { top: 0px !important; position: static !important; }
        .skiptranslate iframe { display: none !important; }
        #goog-gt-tt { display: none !important; }
        .goog-te-spinner-pos { display: none !important; }

    </style>
</head>
<body>
    
    {{-- Top Bar Yesplis Style --}}
    <div class="top-bar d-none d-lg-block">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('admin.login') }}" class="d-flex align-items-center text-white" style="text-decoration: none;">
                    <i class="fas fa-plus me-2" style="font-size: 0.8rem; color: #ffc107;"></i> Daftarkan Eventmu Sekarang
                </a>
            </div>
            <div>
                <ul class="list-inline mb-0">
                    @if(!empty($globalSettings['footer_blog_link']))
                        <li class="list-inline-item me-4"><a href="{{ $globalSettings['footer_blog_link'] }}">Tentang Kami</a></li>
                    @endif
                    @if(!empty($globalSettings['footer_contact_link']))
                        <li class="list-inline-item"><a href="{{ $globalSettings['footer_contact_link'] }}">Customer Service</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- Main Navbar Yesplis Style --}}
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container align-items-center">
            <a class="navbar-brand" href="{{ route('public.index') }}">
                @if(isset($globalSettings['logo_path']) && $globalSettings['logo_path'])
                    <img src="{{ asset('storage/' . $globalSettings['logo_path']) }}" alt="Logo" style="height: 38px; filter: drop-shadow(0px 1px 2px rgba(0,0,0,0.1));">
                @else
                    <i class="fas fa-ticket-alt me-2 text-primary"></i>
                    <span class="text-primary">{{ $globalSettings['site_title'] ?? 'TixKita' }}</span>
                @endif
            </a>
            
            <button class="navbar-toggler collapsed border-0 shadow-none p-0 ms-auto me-3 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <div class="hamburger-icon">
                    <span style="background-color: var(--primary-color);"></span>
                    <span style="background-color: var(--primary-color);"></span>
                    <span style="background-color: var(--primary-color);"></span>
                </div>
            </button>

            <div class="collapse navbar-collapse d-lg-flex justify-content-between w-100" id="navbarNav">
                {{-- Search Bar (Center) --}}
                <form class="search-container mx-auto" role="search" action="{{ route('public.index') }}" method="GET">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="Cari berdasarkan artis, acara, atau nama tempat" aria-label="Search">
                        <button class="btn btn-search" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>

                {{-- Right Navigation --}}
                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item d-lg-none">
                        <a href="{{ route('public.index') }}" class="nav-link border-bottom py-3">Home</a>
                    </li>
                    <li class="nav-item d-lg-none mb-3">
                        <a href="{{ route('public.index', ['view' => 'all']) }}" class="nav-link border-bottom py-3">Events</a>
                    </li>
                    
                    {{-- Language Selector (Functional UI) --}}
                    <li class="nav-item dropdown me-lg-4 d-none d-lg-block">
                        <a class="nav-link dropdown-toggle text-dark fw-bold d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="text-danger fw-bolder me-1" id="current-lang-lbl">ID</span> <i class="fas fa-chevron-down ms-1 text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item fw-semibold" href="javascript:void(0)" onclick="switchLanguage('ID')">ID - Indonesia</a></li>
                            <li><a class="dropdown-item fw-semibold" href="javascript:void(0)" onclick="switchLanguage('EN')">EN - English</a></li>
                        </ul>
                    </li>
                    
                    {{-- Cart Icon --}}
                    <li class="nav-item me-lg-4 my-2 my-lg-0">
                        <a href="{{ route('public.cart.show') }}" class="nav-link text-dark d-flex align-items-center" title="Keranjang">
                            <div class="position-relative">
                                <i class="fas fa-shopping-bag fa-lg" style="color: #4a4a4a; font-size: 1.4rem;"></i>
                                <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; display:none;">0</span>
                            </div>
                            <span class="ms-2 fw-semibold" style="color: #4a4a4a;">Keranjang</span>
                        </a>
                    </li>
                    
                    {{-- Auth Buttons / User Profile --}}
                    <li class="nav-item d-flex flex-column flex-lg-row align-items-lg-center gap-3 mt-2 mt-lg-0 pb-3 pb-lg-0">
                        @auth('customer')
                            {{-- Customer logged in --}}
                            <div class="dropdown w-100 w-lg-auto">
                                <a href="#" class="nav-link text-dark fw-semibold d-inline-flex align-items-center p-0 w-100" 
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="d-flex align-items-center bg-light rounded px-3 py-2 border w-100">
                                        <i class="fas fa-user-circle fa-lg text-primary me-2"></i>
                                        <span class="text-truncate" style="max-width: 120px;">{{ Auth::guard('customer')->user()->name }}</span>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded mt-2 w-100 w-lg-auto">
                                    <li class="px-3 py-2 border-bottom">
                                        <span class="fw-bold">{{ Auth::guard('customer')->user()->name }}</span><br>
                                        <small class="text-muted">{{ Auth::guard('customer')->user()->email }}</small>
                                    </li>
                                    <li><a class="dropdown-item py-2" href="{{ route('customer.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2 text-muted"></i> Dashboard
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('customer.logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item py-2 text-danger">
                                                <i class="fas fa-sign-out-alt me-2"></i> Keluar
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @else
                            {{-- Guest - Yesplis Style Buttons --}}
                            <a href="{{ route('customer.login') }}" class="btn fw-bold px-4 text-primary w-100 w-lg-auto" style="background-color: #f0f4ff; border: none; border-radius: 4px;">Masuk</a>
                            <a href="{{ route('customer.register') }}" class="btn btn-primary fw-bold px-4 w-100 w-lg-auto" style="border-radius: 4px;">Daftar</a>
                        @endauth
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- Hero Section (Optional, jika halaman mendefinisikannya) --}}
    @yield('hero')

    <main class="py-2 py-md-4">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">{{ $globalSettings['site_title'] ?? 'TixKita' }}</h5>
                    <p class="text-muted small">Platform terbaik untuk menemukan pengalaman baru dan travel kit untuk petualanganmu.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="fw-bold">Tentang Kami</h6>
                    <ul class="list-unstyled small text-muted">
                        @if(!empty($globalSettings['footer_blog_link']))
                            <li><a href="{{ $globalSettings['footer_blog_link'] }}" class="text-decoration-none text-muted">Blog</a></li>
                        @endif
                        @if(!empty($globalSettings['footer_career_link']))
                            <li><a href="{{ $globalSettings['footer_career_link'] }}" class="text-decoration-none text-muted">Karir</a></li>
                        @endif
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="fw-bold">Bantuan</h6>
                    <ul class="list-unstyled small text-muted">
                        @if(!empty($globalSettings['footer_contact_link']))
                            <li><a href="{{ $globalSettings['footer_contact_link'] }}" class="text-decoration-none text-muted">Hubungi Kami</a></li>
                        @endif
                        @if(!empty($globalSettings['footer_faq_link']))
                            <li><a href="{{ $globalSettings['footer_faq_link'] }}" class="text-decoration-none text-muted">FAQ</a></li>
                        @endif
                        <li><a href="{{ route('admin.login') }}" class="text-decoration-none text-muted">Login Organizer</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold">Ikuti Kami</h6>
                    <div class="d-flex gap-3">
                        @if(!empty($globalSettings['social_instagram']))
                            <a href="{{ $globalSettings['social_instagram'] }}" class="text-white" target="_blank"><i class="fab fa-instagram fa-lg"></i></a>
                        @endif
                        @if(!empty($globalSettings['social_twitter']))
                            <a href="{{ $globalSettings['social_twitter'] }}" class="text-white" target="_blank"><i class="fab fa-twitter fa-lg"></i></a>
                        @endif
                        @if(!empty($globalSettings['social_facebook']))
                            <a href="{{ $globalSettings['social_facebook'] }}" class="text-white" target="_blank"><i class="fab fa-facebook fa-lg"></i></a>
                        @endif
                        @if(!empty($globalSettings['social_linkedin']))
                            <a href="{{ $globalSettings['social_linkedin'] }}" class="text-white" target="_blank"><i class="fab fa-linkedin fa-lg"></i></a>
                        @endif
                    </div>
                    <p class="small text-muted mt-3 mb-0">&copy; 2025 {{ $globalSettings['site_title'] ?? 'TixKita' }} Project.</p>
                    <p class="small text-muted mb-0">Powered by <a href="https://gridkita.my.id" target="_blank" class="text-decoration-none fw-semibold" style="color: var(--primary-color);">GridKita</a></p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/cart.js') }}?v={{ time() }}"></script> 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Konfigurasi Desain Global
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Fungsi Global untuk Alert "Center" yang cantik
        window.showAlert = function(type, title, text) {
            Swal.fire({
                icon: type,
                title: title,
                text: text,
                confirmButtonText: 'OK',
                confirmButtonColor: '{{ $primaryColor }}',
                buttonsStyling: true,
                customClass: {
                    confirmButton: 'btn btn-primary-custom px-4 py-2'
                },
                showClass: {
                    popup: 'animate__animated animate__zoomIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut'
                }
            });
        };

        // Handle Session Flash Messages
        @if(session('success'))
            window.showAlert('success', 'Berhasil!', "{{ session('success') }}");
        @endif
        @if(session('error'))
            window.showAlert('error', 'Terjadi Kesalahan', "{{ session('error') }}");
        @endif

        // Internal Language Dictionary
        const translations = {
            'ID': {
                'Daftarkan Eventmu Sekarang': 'Daftarkan Eventmu Sekarang',
                'Tentang Kami': 'Tentang Kami',
                'Customer Service': 'Customer Service',
                'Cari berdasarkan artis, acara, atau nama tempat': 'Cari berdasarkan artis, acara, atau nama tempat',
                'Keranjang': 'Keranjang',
                'Masuk': 'Masuk',
                'Daftar': 'Daftar',
                'Home': 'Home',
                'Events': 'Events',
                'Bantuan': 'Bantuan',
                'Ikuti Kami': 'Ikuti Kami',
                'Login Organizer': 'Login Organizer',
                'Hubungi Kami': 'Hubungi Kami',
                'Platform terbaik untuk menemukan pengalaman baru dan travel kit untuk petualanganmu.': 'Platform terbaik untuk menemukan pengalaman baru dan travel kit untuk petualanganmu.',
                'Event Pilihan & Terlaris': 'Event Pilihan & Terlaris',
                'Lihat Semua': 'Lihat Semua',
                'Semua Event': 'Semua Event',
                'Kembali': 'Kembali',
                'Belum ada acara yang tersedia saat ini.': 'Belum ada acara yang tersedia saat ini.',
                'Reset Pencarian': 'Reset Pencarian',
                'Tentang Event Ini': 'Tentang Event Ini',
                'Pilih Tiket & Kit': 'Pilih Tiket & Kit',
                'Silakan pilih kategori tiket atau merchandise yang tersedia.': 'Silakan pilih kategori tiket atau merchandise yang tersedia.',
                'Lihat Keranjang': 'Lihat Keranjang',
                'Bagikan': 'Bagikan',
                'Beli Tiket': 'Beli Tiket',
                'Keranjang Kosong': 'Keranjang Kosong',
                'Lanjut Pembayaran': 'Lanjut Pembayaran',
                'Ringkasan Pesanan': 'Ringkasan Pesanan',
                'Informasi Pemesan': 'Informasi Pemesan',
                'Pilih Metode Pembayaran': 'Pilih Metode Pembayaran',
                'Bayar Sekarang': 'Bayar Sekarang',
                'Harga': 'Harga',
                'Jumlah': 'Jumlah',
                'Total': 'Total',
                'Subtotal': 'Subtotal',
                'Tersedia': 'Tersedia',
                'Habis': 'Habis',
                'Sold Out': 'Sold Out'
            },
            'EN': {
                'Daftarkan Eventmu Sekarang': 'Register Your Event Now',
                'Tentang Kami': 'About Us',
                'Customer Service': 'Customer Support',
                'Cari berdasarkan artis, acara, atau nama tempat': 'Search by artist, event, or venue name',
                'Keranjang': 'Cart',
                'Masuk': 'Login',
                'Daftar': 'Register',
                'Home': 'Home',
                'Events': 'Events',
                'Bantuan': 'Help',
                'Ikuti Kami': 'Follow Us',
                'Login Organizer': 'Organizer Login',
                'Hubungi Kami': 'Contact Us',
                'Platform terbaik untuk menemukan pengalaman baru dan travel kit untuk petualanganmu.': 'The best platform to discover new experiences and travel kits for your adventures.',
                'Event Pilihan & Terlaris': 'Featured & Best Selling Events',
                'Lihat Semua': 'View All',
                'Semua Event': 'All Events',
                'Kembali': 'Back',
                'Belum ada acara yang tersedia saat ini.': 'No events are currently available.',
                'Reset Pencarian': 'Reset Search',
                'Tentang Event Ini': 'About This Event',
                'Pilih Tiket & Kit': 'Select Ticket & Kit',
                'Silakan pilih kategori tiket atau merchandise yang tersedia.': 'Please select an available ticket category or merchandise.',
                'Lihat Keranjang': 'View Cart',
                'Bagikan': 'Share',
                'Beli Tiket': 'Buy Ticket',
                'Keranjang Kosong': 'Cart is Empty',
                'Lanjut Pembayaran': 'Proceed to Checkout',
                'Ringkasan Pesanan': 'Order Summary',
                'Informasi Pemesan': 'Buyer Information',
                'Pilih Metode Pembayaran': 'Select Payment Method',
                'Bayar Sekarang': 'Pay Now',
                'Harga': 'Price',
                'Jumlah': 'Quantity',
                'Total': 'Total',
                'Subtotal': 'Subtotal',
                'Tersedia': 'Available',
                'Habis': 'Out of Stock',
                'Sold Out': 'Sold Out'
            }
        };

        function switchLanguage(lang) {
            // Save to local storage
            localStorage.setItem('tixkita_lang', lang);
            
            // Update UI label
            const lbl = document.getElementById('current-lang-lbl');
            if (lbl) {
                if (lang === 'EN') {
                    lbl.innerText = 'EN';
                    lbl.className = 'text-primary fw-bolder me-1';
                } else {
                    lbl.innerText = 'ID';
                    lbl.className = 'text-danger fw-bolder me-1';
                }
            }
            
            // Apply translations to specific elements
            applyTranslations(lang);
        }

        function applyTranslations(lang) {
            const dict = translations[lang];
            if(!dict) return;
            
            // Translate placeholders
            document.querySelectorAll('input[placeholder="Cari berdasarkan artis, acara, atau nama tempat"]').forEach(el => {
                el.placeholder = dict['Cari berdasarkan artis, acara, atau nama tempat'];
            });
            
            // Translate text nodes
            const walk = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
            let n;
            const idDict = translations['ID'];
            
            while(n = walk.nextNode()) {
                let text = n.nodeValue.trim();
                if(text === '') continue;
                
                // If current text matches ID dictionary, replace with target language
                for (const [key, value] of Object.entries(idDict)) {
                    if (text === value) {
                        n.nodeValue = n.nodeValue.replace(value, dict[key]);
                        break;
                    }
                }
                
                // Also check if current text matches EN dictionary and we want to go back to ID
                if (lang === 'ID') {
                    for (const [key, value] of Object.entries(translations['EN'])) {
                        if (text === value) {
                            n.nodeValue = n.nodeValue.replace(value, idDict[key]);
                            break;
                        }
                    }
                }
            }
        }

        // Cek bahasa aktif saat halaman dimuat
        document.addEventListener("DOMContentLoaded", function() {
            let currentLang = localStorage.getItem('tixkita_lang') || 'ID';
            
            const lbl = document.getElementById('current-lang-lbl');
            if (lbl) {
                if (currentLang === 'EN') {
                    lbl.innerText = 'EN';
                    lbl.className = 'text-primary fw-bolder me-1';
                } else {
                    lbl.innerText = 'ID';
                    lbl.className = 'text-danger fw-bolder me-1';
                }
            }
            
            if(currentLang === 'EN') {
                applyTranslations('EN');
            }
        });

        // Auto Close Navbar on Click Outside (Mobile)
        document.addEventListener('click', function(event) {
            var navbarCollapse = document.getElementById('navbarNav');
            var toggler = document.querySelector('.navbar-toggler');
            
            if (navbarCollapse && toggler) {
                var isClickInside = navbarCollapse.contains(event.target) || toggler.contains(event.target);
                var isOpened = navbarCollapse.classList.contains('show');

                if (!isClickInside && isOpened) {
                    var bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    } else {
                        new bootstrap.Collapse(navbarCollapse).hide();
                    }
                }
            }
        });
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    @yield('scripts')
</body>
</html>