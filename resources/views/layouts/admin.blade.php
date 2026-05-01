<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TixKita</title>
    
    {{-- Prevent Browser Cache (Important for logout) --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    @if(isset($globalSettings['favicon_path']) && $globalSettings['favicon_path'])
        <link rel="icon" href="{{ asset('storage/' . $globalSettings['favicon_path']) }}">
    @endif
    
    {{-- Fonts & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    
    @php
        $primaryColor = $globalSettings['primary_color'] ?? '#3a7d44';
        $secondaryColor = $globalSettings['secondary_color'] ?? '#2e6636';
        $logoPath = $globalSettings['logo_path'] ?? null;
    @endphp

    <style>
        :root {
            --admin-primary: {{ $primaryColor }};
            --admin-secondary: {{ $secondaryColor }};
        }
        /* Override Bootstrap primary & secondary colors */
        .text-primary { color: var(--admin-primary) !important; }
        .text-secondary { color: var(--admin-secondary) !important; }
        
        .bg-primary { background-color: var(--admin-primary) !important; }
        .bg-secondary { background-color: var(--admin-secondary) !important; }
        
        .btn-primary { background-color: var(--admin-primary) !important; border-color: var(--admin-primary) !important; color: white !important; }
        .btn-primary:hover { background-color: var(--admin-secondary) !important; border-color: var(--admin-secondary) !important; color: white !important; }
        
        .btn-secondary { background-color: var(--admin-secondary) !important; border-color: var(--admin-secondary) !important; color: white !important; }
        .btn-secondary:hover { background-color: var(--admin-primary) !important; border-color: var(--admin-primary) !important; color: white !important; }
        
        .btn-outline-primary { color: var(--admin-primary) !important; border-color: var(--admin-primary) !important; background-color: transparent !important; }
        .btn-outline-primary:hover { background-color: var(--admin-primary) !important; border-color: var(--admin-primary) !important; color: #fff !important; }
        
        .btn-outline-secondary { color: var(--admin-secondary) !important; border-color: var(--admin-secondary) !important; background-color: transparent !important; }
        .btn-outline-secondary:hover { background-color: var(--admin-secondary) !important; border-color: var(--admin-secondary) !important; color: white !important; }

        .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
            background-color: var(--admin-primary) !important;
            color: white !important;
        }
        
        /* Ensures inactive nav-links don't use default blue */
        .nav-pills .nav-link { color: var(--admin-secondary); }
        .nav-pills .nav-link:hover { color: var(--admin-primary); }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            overflow-x: hidden;
        }

        /* --- SIDEBAR STYLE --- */
        #sidebar-wrapper {
            height: 100vh;
            width: 250px;
            background: linear-gradient(180deg, var(--admin-primary) 10%, var(--admin-secondary) 100%);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
            scrollbar-width: thin; /* Untuk Firefox agar scrollbar tidak terlalu lebar */
        }
        
        /* Custom scrollbar style for Webkit (Chrome/Safari) */
        #sidebar-wrapper::-webkit-scrollbar {
            width: 5px;
        }
        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }
        #sidebar-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.4);
        }
        .sidebar-brand {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }
        .list-group-item {
            background-color: transparent;
            color: rgba(255,255,255,0.8);
            border: none;
            padding: 0.75rem 1.25rem; /* Lebih compact */
            font-weight: 600;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        .list-group-item:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        .list-group-item.active-link {
            color: #fff;
            font-weight: 700;
            border-left: 4px solid #fff;
            background-color: rgba(255,255,255,0.1);
        }
        
        /* Submenu Styling */
        .submenu .list-group-item {
            padding-left: 3rem !important; /* Indentasi submenu */
            padding-top: 0.6rem;
            padding-bottom: 0.6rem;
            font-size: 0.85rem;
            border-left: 4px solid transparent; /* Placeholder border agar tidak goyang saat active */
        }
        .submenu .list-group-item:hover {
            padding-left: 3.2rem !important; /* Efek hover geser sedikit */
        }
        .submenu .list-group-item.active-link {
            border-left: 4px solid #fff;
            background-color: rgba(255,255,255,0.05);
        }
        .list-group-item i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }

        /* --- CONTENT WRAPPER --- */
        #page-content-wrapper {
            margin-left: 250px; /* Lebar Sidebar */
            width: calc(100% - 250px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* --- TOP NAVBAR --- */
        .top-navbar {
            height: 70px;
            background-color: #fff;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            justify-content: flex-end;
        }

        /* --- CARD STYLE UMUM --- */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 700;
            color: var(--admin-primary);
            padding: 1rem 1.5rem;
        }

        /* --- CUSTOM BADGES (SOFT COLORS) --- */
        .bg-soft-success { background-color: #d1e7dd; color: #0f5132; }
        .bg-soft-warning { background-color: #fff3cd; color: #664d03; }
        .bg-soft-danger { background-color: #f8d7da; color: #842029; }
        .bg-soft-info { background-color: #cff4fc; color: #055160; }
        .bg-soft-secondary { background-color: #e2e3e5; color: #41464b; }

        /* --- FIX PAGINATION LARAVEL (PENTING) --- */
        /* Kode ini memperbaiki ikon pagination yang terlalu besar */
        .pagination {
            margin-bottom: 0;
            gap: 5px;
        }
        .page-item .page-link {
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
            color: var(--admin-primary);
            border-radius: 0.35rem;
            border: 1px solid #dddfeb;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .page-item.active .page-link {
            background-color: var(--admin-primary);
            border-color: var(--admin-primary);
            color: white;
        }
        .page-item.disabled .page-link {
            color: #858796;
            background-color: #fff;
            border-color: #dddfeb;
        }
        /* Memaksa ukuran ikon SVG (panah) menjadi kecil */
        .page-item .page-link svg {
            width: 14px !important;
            height: 14px !important;
        }
        /* Menyembunyikan teks "Previous/Next" bawaan Tailwind jika muncul */
        .w-5.h-5 {
            width: 16px;
            height: 16px;
        }

        /* --- RESPONSIF --- */
        /* --- HAMBURGER ANIMATION --- */
        .hamburger-icon {
            width: 24px;
            height: 22px; /* Increased to fit 3 lines */
            position: relative;
            cursor: pointer;
            display: inline-block;
        }
        .hamburger-icon span {
            display: block;
            position: absolute;
            height: 3px;
            width: 100%;
            background: var(--admin-primary); /* Primary Color */
            border-radius: 3px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }
        .hamburger-icon span:nth-child(1) { top: 0px; }
        .hamburger-icon span:nth-child(2) { top: 9px; } /* Center */
        .hamburger-icon span:nth-child(3) { top: 18px; }

        /* State: Active (X) */
        .hamburger-icon.active span:nth-child(1) {
            top: 9px;
            transform: rotate(135deg);
        }
        .hamburger-icon.active span:nth-child(2) {
            opacity: 0;
            left: -30px; /* Fly out */
        }
        .hamburger-icon.active span:nth-child(3) {
            top: 9px;
            transform: rotate(-135deg);
        }

        /* --- RESPONSIF --- */
        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: -250px;
                box-shadow: none; /* Hilangkan shadow saat hidden */
            }
            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0;
                box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15); /* Shadow saat muncul */
            }
            #page-content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            /* Overlay content when toggled (Optional, tapi lebih rapi jika content digeser atau overlay) */
            /* Kita gunakan style geser content/tetap full width tapi sidebar menutupi */
            
            /* Typography adjustments */
            h1.h3 { font-size: 1.5rem; }
            .card-header { padding: 0.75rem 1rem; }
            
            /* Space Saving */
            .px-4 { padding-left: 1rem !important; padding-right: 1rem !important; }
        }

    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
        
        {{-- ============================================== --}}
        {{-- SIDEBAR NAVIGASI --}}
        {{-- ============================================== --}}
        <div id="sidebar-wrapper">
            <div class="sidebar-brand">
                @if($logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="TixKita" style="height: 40px;">
                @else
                    <i class="fas fa-ticket-alt" style="font-size: 1.5rem;"></i>
                @endif
            </div>
            
            <div class="list-group list-group-flush mt-3">
                @if(Auth::user()->role === 'scanner')
                    {{-- MENU KHUSUS SCANNER --}}
                    <div class="small text-white-50 ps-4 mb-2 text-uppercase fw-bold" style="font-size: 0.7rem;">Scanner Area</div>
                    
                    <a href="{{ route('scanner.index') }}" class="list-group-item list-group-item-action border-0 @if(request()->routeIs('scanner.index')) active-link @endif">
                        <i class="fas fa-fw fa-camera"></i> Kamera Scanner
                    </a>
                    
                    <a href="{{ route('admin.bookings.index') }}" class="list-group-item list-group-item-action border-0 @if(request()->routeIs('admin.bookings*')) active-link @endif">
                        <i class="fas fa-fw fa-list"></i> Riwayat Scan
                    </a>
                @else
                    {{-- MENU UTAMA (ADMIN & SUPERADMIN) --}}
                    <div class="small text-white-50 ps-4 mb-2 text-uppercase fw-bold" style="font-size: 0.7rem;">Menu Utama</div>
                    
                    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action border-0 @if(request()->routeIs('admin.dashboard')) active-link @endif">
                        <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                    </a>
                    
                    {{-- MANAJEMEN DATA --}}
                    <a class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center mt-2" data-bs-toggle="collapse" href="#collapseManajemenData" role="button" 
                       aria-expanded="{{ request()->routeIs('admin.events*', 'admin.products*', 'admin.locations*', 'admin.bookings*', 'admin.customers*') ? 'true' : 'false' }}" aria-controls="collapseManajemenData">
                        <span>
                            <i class="fas fa-fw fa-database"></i> Manajemen Data
                        </span>
                        <i class="fas fa-chevron-down small" style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.events*', 'admin.products*', 'admin.locations*', 'admin.bookings*', 'admin.customers*') ? 'show' : '' }}" id="collapseManajemenData">
                        <div class="submenu bg-black bg-opacity-10">
                            <a href="{{ route('admin.events') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.events*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-calendar-alt"></i> Daftar Event
                            </a>
                            <a href="{{ route('admin.bookings.index') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.bookings*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-qrcode"></i> Bookings / Scan
                            </a>
                            <a href="{{ route('admin.products.index') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.products*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-box-open"></i> Produk & Kit
                            </a>
                            @if(Auth::user()->isSuperAdmin())
                            <a href="{{ route('admin.locations.index') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.locations*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-map-marked-alt"></i> Lokasi
                            </a>
                            <a href="{{ route('admin.customers.index') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.customers*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-users"></i> Data Pelanggan
                            </a>
                            @endif
                        </div>
                    </div>
                    
                    {{-- TRANSAKSI --}}
                    <a class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center mt-1" data-bs-toggle="collapse" href="#collapseTransaksi" role="button" 
                       aria-expanded="{{ request()->routeIs('admin.orders*', 'admin.discounts*') ? 'true' : 'false' }}" aria-controls="collapseTransaksi">
                        <span>
                            <i class="fas fa-fw fa-cash-register"></i> Transaksi
                        </span>
                        <i class="fas fa-chevron-down small" style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.orders*', 'admin.discounts*', 'admin.sponsorships*') ? 'show' : '' }}" id="collapseTransaksi">
                        <div class="submenu bg-black bg-opacity-10">
                            <a href="{{ route('admin.orders') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.orders*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-shopping-cart"></i> Pesanan Masuk
                            </a>
                            <a href="{{ route('admin.sponsorships.index') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.sponsorships*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-handshake"></i> Sponsorship
                            </a>
                            <a href="{{ route('admin.discounts.index') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.discounts*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-tags"></i> Kode Diskon
                            </a>
                        </div>
                    </div>

                    <a class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center mt-1" data-bs-toggle="collapse" href="#collapseSistem" role="button" 
                       aria-expanded="{{ request()->routeIs('admin.settings*', 'admin.transaction.settings*', 'admin.users*', 'admin.banners*', 'admin.password*', 'admin.organizer*', 'admin.categories*') ? 'true' : 'false' }}" aria-controls="collapseSistem">
                        <span>
                            <i class="fas fa-fw fa-cogs"></i> Sistem
                        </span>
                        <i class="fas fa-chevron-down small" style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.settings*', 'admin.transaction.settings*', 'admin.users*', 'admin.banners*', 'admin.password*', 'admin.organizer*', 'admin.categories*') ? 'show' : '' }}" id="collapseSistem">
                        <div class="submenu bg-black bg-opacity-10">
                            @if(Auth::user()->isSuperAdmin())
                                <a href="{{ route('admin.banners.index') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.banners*')) active-link text-white @endif">
                                    <i class="fas fa-fw fa-images"></i> Manajemen Banner
                                </a>
                                <a href="{{ route('admin.settings') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.settings*')) active-link text-white @endif">
                                    <i class="fas fa-fw fa-sliders-h"></i> Pengaturan Web
                                </a>
                                <a href="{{ route('admin.transaction.settings') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.transaction.settings*')) active-link text-white @endif">
                                    <i class="fas fa-fw fa-calculator"></i> Pengaturan Transaksi
                                </a>
                                <a href="{{ route('admin.categories.index') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.categories*')) active-link text-white @endif">
                                    <i class="fas fa-fw fa-tags"></i> Kategori Event
                                </a>

                            @endif
                            
                            {{-- Manajemen Akun --}}
                            <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.users*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-users-cog"></i> Manajemen Akun
                            </a>
                            {{-- Profil Organizer --}}
                            <a href="{{ route('admin.organizer.profile') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.organizer*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-id-card"></i> Profil Organizer
                            </a>
                            {{-- Change Password --}}
                            <a href="{{ route('admin.password.form') }}" class="list-group-item list-group-item-action border-0 ps-5 text-white-50 @if(request()->routeIs('admin.password*')) active-link text-white @endif">
                                <i class="fas fa-fw fa-key"></i> Change Password
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Logout Button di Sidebar Bawah --}}
                <div class="mt-auto px-3 mb-4 pt-4">
                    <form action="{{ route('admin.logout') }}" method="POST" id="logout-form">
                        @csrf
                        <button type="submit" class="btn btn-outline-light w-100 py-2 rounded-pill shadow-sm fw-bold border-0 bg-white bg-opacity-10" style="font-size: 0.9rem;" onclick="confirmLogout(event)">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ============================================== --}}
        {{-- PAGE CONTENT WRAPPER --}}
        {{-- ============================================== --}}
        <div id="page-content-wrapper">
            
            {{-- Top Navbar --}}
            <nav class="top-navbar mb-4 sticky-top d-flex justify-content-between align-items-center">
                {{-- Toggle Button (Mobile Only) --}}
                <button class="btn btn-link d-md-none rounded-circle mr-3 p-0" id="menu-toggle">
                    <div class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </button>

                {{-- User Info (Right Aligned) --}}
                <div class="d-flex align-items-center dropdown ms-auto">
                    <a class="nav-link dropdown-toggle d-flex align-items-center text-decoration-none" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2 text-gray-600 small d-none d-sm-inline">Halo, <strong>{{ Auth::user()->name ?? 'Administrator' }}</strong></span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 35px; height: 35px; background: var(--admin-primary);">
                            <i class="fas fa-user"></i>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow animated--grow-in px-2" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item rounded small py-2" href="{{ route('admin.password.form') }}">
                                <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                                Ubah Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item rounded small py-2 text-danger" href="#" onclick="confirmLogout(event)">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            {{-- Main Content Container --}}
            <div class="container-fluid px-4 pb-5">
                
                {{-- Global Alert Success/Error (Digantikan oleh SweetAlert2 Toast di Layout Bawah) --}}
                {{-- Block alert lama dihapus agar clean --}}

                {{-- Konten Halaman --}}
                @yield('content')
            </div>

            {{-- Footer --}}
            <footer class="bg-white sticky-footer mt-auto py-3 border-top">
                <div class="container my-auto">
                    <div class="text-center my-auto text-muted small">
                        <span>Copyright &copy; TixKita Admin {{ date('Y') }}</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- Script Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    {{-- Force Reload on Back Button (Bypass bfcache) --}}
    <script>
        // Detect back/forward navigation and force reload
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                // Page is loaded from cache (back/forward button), force reload
                window.location.reload();
            }
        });
    </script>
    
    {{-- Slot untuk Script Spesifik Halaman (Misal: Chart/AJAX) --}}
    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle Sidebar
            $("#menu-toggle").click(function(e) {
                e.preventDefault();
                $("#wrapper").toggleClass("toggled");
                // Animate Icon
                $(this).find('.hamburger-icon').toggleClass('active');
            });
            
            // Auto-hide sidebar on mobile when link clicked (better UX)
            if ($(window).width() < 768) {
                $(".list-group-item:not([data-bs-toggle])").click(function() {
                    $("#wrapper").removeClass("toggled");
                    // Reset icon animation
                    $(".hamburger-icon").removeClass('active');
                });
            }
            // Close sidebar when clicking outside (Mobile)
            $(document).click(function(event) {
                var clickover = $(event.target);
                var _opened = $("#wrapper").hasClass("toggled");
                if (_opened === true && !clickover.closest('#sidebar-wrapper').length && !clickover.closest('#menu-toggle').length) {
                    $("#wrapper").removeClass("toggled");
                    $(".hamburger-icon").removeClass('active');
                }
            });
        });
    </script>
    @yield('scripts')

    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // --- 1. Konfigurasi Toast Global ---
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

        // --- 2. Tampilkan Flash Message (Success/Error) via Toast ---
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: {!! json_encode(session('success')) !!}
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: 'Gagal!',
                text: {!! json_encode(session('error')) !!}
            });
        @endif

        // --- 3. Fungsi Global: Konfirmasi Aksi (Delete/Lainnya) ---
        /**
         * Menampilkan popup konfirmasi sebelum submit form.
         * @param {Event} event - Event click button
         * @param {String} title - Judul popup
         * @param {String} text - Pesan detail
         * @param {String|HTMLElement} formRef - ID form atau elemen form itu sendiri
         */
        function confirmAction(event, title, text, formRef) {
            event.preventDefault(); // Stop submit otomatis

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Cek apakah formRef adalah ID string atau elemen DOM
                    if (typeof formRef === 'string') {
                        document.getElementById(formRef).submit();
                    } else {
                        formRef.submit();
                    }
                }
            });
        }
        
        // --- 4. Fungsi Khusus Logout ---
        function confirmLogout(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: "Apakah Anda yakin ingin keluar dari sistem?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }
    </script>
</body>
</html>