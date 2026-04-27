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
        .text-primary-custom { color: var(--primary-color) !important; }
        .text-secondary-custom { color: var(--secondary-color) !important; }
        .bg-primary-custom { background-color: var(--primary-color) !important; }
        .bg-secondary-custom { background-color: var(--secondary-color) !important; }
        
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white; /* Asumsi teks putih di atas warna utama */
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 600;
        }
        .btn-primary-custom:hover, .btn-primary-custom:focus, .btn-primary-custom:active {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }

        .btn-outline-primary-custom {
            color: var(--primary-color);
            border-color: var(--primary-color);
            background-color: transparent;
        }
        .btn-outline-primary-custom:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Navbar Loket Style */
        .navbar {
            background: var(--header-bg);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 800;
            color: var(--header-text) !important;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        .nav-link {
            color: var(--header-text) !important;
        }
        /* Navbar icons fix if using header text color */
        .navbar .fa, .navbar .fas {
            color: var(--header-text);
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

    </style>
</head>
<body>
    
    {{-- Navbar Putih Bersih --}}
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('public.index') }}">
                @if(isset($globalSettings['logo_path']) && $globalSettings['logo_path'])
                    <img src="{{ asset('storage/' . $globalSettings['logo_path']) }}" alt="Logo" style="height: 40px;">
                @else
                    <i class="fas fa-ticket-alt me-2"></i>
                    {{ $globalSettings['site_title'] ?? 'TixKita' }}
                @endif
            </a>
            
            <button class="navbar-toggler collapsed border-0 shadow-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <div class="hamburger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                {{-- Search Bar (Full Width on Mobile) --}}
                <form class="d-flex mx-auto my-3 my-lg-0 w-100" style="max-width: 500px;" role="search" action="{{ route('public.index') }}" method="GET">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 ps-3"><i class="fas fa-search text-muted"></i></span>
                        <input class="form-control bg-light border-0 py-2" type="search" name="q" value="{{ request('q') }}" placeholder="Cari event seru..." aria-label="Search">
                    </div>
                </form>

                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item d-lg-none">
                        <a href="{{ route('public.index') }}" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item d-lg-none">
                        <a href="{{ route('public.index', ['view' => 'all']) }}" class="nav-link">Events</a>
                    </li>
                    
                    
                    <li class="nav-item me-lg-3 mt-3 mt-lg-0">
                        <a href="{{ route('public.cart.show') }}" class="nav-link position-relative text-dark fw-semibold d-inline-block">
                            <i class="fas fa-shopping-bag fa-lg me-2 me-lg-0"></i>
                            <span class="d-lg-none">Keranjang</span>
                            <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; display:none;">0</span>
                        </a>
                    </li>
                    
                    {{-- Login / User Icon --}}
                    <li class="nav-item mt-3 mt-lg-0">
                        @auth('customer')
                            {{-- Customer logged in --}}
                            {{-- Mobile: Name links directly to dashboard --}}
                            <a href="{{ route('customer.dashboard') }}" class="nav-link text-dark fw-semibold d-inline-flex align-items-center d-lg-none">
                                <i class="fas fa-user-circle fa-lg me-2 text-primary-custom"></i>
                                {{ Auth::guard('customer')->user()->name }}
                            </a>
                            {{-- Desktop: Icon opens dropdown --}}
                            <div class="dropdown d-none d-lg-inline-block">
                                <a href="#" class="nav-link text-dark fw-semibold d-inline-flex align-items-center" 
                                   data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle fa-lg text-primary-custom"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
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
                            {{-- Guest - Show Login Button --}}
                            <a href="{{ route('customer.login') }}" class="nav-link text-dark fw-semibold d-inline-block" title="Masuk">
                                <i class="far fa-user fa-lg me-2 me-lg-0"></i>
                                <span class="d-lg-none">Masuk</span>
                            </a>
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

        // Auto Close Navbar on Click Outside (Mobile)
        document.addEventListener('click', function(event) {
            var navbarCollapse = document.getElementById('navbarNav');
            var toggler = document.querySelector('.navbar-toggler');
            
            // Check if element exists before proceeding
            if (navbarCollapse && toggler) {
                var isClickInside = navbarCollapse.contains(event.target) || toggler.contains(event.target);
                var isOpened = navbarCollapse.classList.contains('show');

                if (!isClickInside && isOpened) {
                    // Use Bootstrap 5 API to hide
                    var bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    } else {
                        // Fallback if instance not found
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