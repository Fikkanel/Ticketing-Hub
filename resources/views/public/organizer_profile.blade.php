@extends('layouts.public')

@section('title', $organizer->organizer_name ?? $organizer->name . ' - Organizer')

@section('content')
    {{-- HEADER ORGANIZER (Pockets.id Style) --}}
    <div class="organizer-header">
        <div class="container">
            <div class="organizer-header-content">
                {{-- Logo Card (Mobile: Floating Card Style) --}}
                <div class="organizer-logo-card">
                    <img src="{{ $organizer->organizer_logo_src }}" 
                         alt="{{ $organizer->organizer_name }}" 
                         class="organizer-logo-img">
                    <span class="organizer-logo-label">{{ Str::limit($organizer->organizer_name ?? $organizer->name, 12) }}</span>
                </div>
                
                {{-- Info --}}
                <div class="organizer-info">
                    <h1 class="organizer-name">{{ $organizer->organizer_name ?? $organizer->name }}</h1>
                    @if($organizer->organizer_name && $organizer->organizer_name !== $organizer->name)
                        <p class="organizer-subtitle">{{ $organizer->name }}</p>
                    @endif
                    <p class="organizer-member-since">
                        Member since {{ $organizer->member_since }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container organizer-content">
        {{-- MOBILE: Info Card (Hidden on Desktop) --}}
        <div class="organizer-info-card d-lg-none mb-4">
            <div class="info-row">
                <span class="info-label"><i class="fas fa-calendar-check me-2"></i>Total Events</span>
                <span class="info-value">{{ $events->count() }}</span>
            </div>
            @if($organizer->organizer_city)
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-map-marker-alt me-2"></i>Lokasi</span>
                    <span class="info-value">{{ $organizer->organizer_city }}</span>
                </div>
            @endif
            @if($organizer->organizer_phone)
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-phone me-2"></i>Phone</span>
                    <span class="info-value">{{ $organizer->organizer_phone }}</span>
                </div>
            @endif
            @if($organizer->organizer_instagram)
                <div class="info-row">
                    <span class="info-label"><i class="fab fa-instagram me-2"></i>Instagram</span>
                    <a href="{{ $organizer->organizer_instagram }}" target="_blank" class="info-value text-decoration-none">
                        <i class="fas fa-external-link-alt"></i> Follow
                    </a>
                </div>
            @endif
            @if($organizer->organizer_phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $organizer->organizer_phone) }}" 
                   target="_blank" 
                   class="btn btn-success w-100 mt-3 py-2 fw-bold rounded-pill">
                    <i class="fab fa-whatsapp me-2"></i> Hubungi Organizer
                </a>
            @endif
        </div>

        <div class="row">
            {{-- KOLOM KIRI: Daftar Event --}}
            <div class="col-lg-8">
                {{-- Section Title (Desktop Only) --}}
                <div class="section-title d-none d-lg-block mb-4">
                    <h4 class="fw-bold text-dark">
                        <i class="fas fa-calendar-alt me-2 text-primary-custom"></i>
                        Event oleh {{ $organizer->organizer_name ?? $organizer->name }}
                    </h4>
                </div>

                {{-- Grid Event (2 columns on mobile, 3 on desktop to match homepage) --}}
                <div class="row g-2 event-grid">
                    @forelse($events as $event)
                        <div class="col-6 col-lg-4">
                            {{-- Card Event (Same as Homepage) --}}
                            <div class="card card-event border-0 shadow-sm rounded-3 overflow-hidden h-100">
                                <a href="{{ route('public.event.detail', $event->event_id) }}" class="text-decoration-none text-dark">
                                    <div class="position-relative">
                                        {{-- Gambar --}}
                                        <img src="{{ $event->card_src }}" 
                                             alt="{{ $event->judul }}"
                                             class="w-100 event-card-img"
                                             loading="lazy">
                                        {{-- Badge Status --}}
                                        <span class="position-absolute top-0 end-0 m-1 badge bg-white text-dark shadow-sm rounded-pill px-2 py-1 small fw-bold" style="font-size: 0.5rem;">
                                            {{ $event->status }}
                                        </span>
                                    </div>
                                    
                                    {{-- Date Strip (Pockets Style - Same as Homepage) --}}
                                    <div class="px-3 py-2 bg-primary-custom text-white small d-flex align-items-center date-strip" style="font-size: 0.75rem;">
                                        <i class="far fa-calendar-alt me-2"></i> 
                                        {{ \Carbon\Carbon::parse($event->tgl_mulai)->format('d M y') }}
                                        <span class="mx-1">|</span>
                                        {{ \Carbon\Carbon::parse($event->tgl_mulai)->format('H:i') }}
                                    </div>

                                    <div class="card-body p-3 d-flex flex-column">
                                        {{-- Judul --}}
                                        <h6 class="card-title fw-bold mb-1 text-truncate" style="font-size: 1rem;">{{ $event->judul }}</h6>
                                        
                                        {{-- Lokasi --}}
                                        <div class="small text-muted mb-2 text-truncate event-location">
                                            <i class="fas fa-map-marker-alt me-1 text-secondary-custom"></i> 
                                            {{ $event->location->kota ?? 'Online' }}
                                        </div>
                                        
                                        {{-- Harga --}}
                                        <div class="small fw-bold text-primary-custom text-end event-price mt-auto">
                                            @if($event->min_price > 0)
                                                Starts Rp {{ number_format($event->min_price, 0, ',', '.') }}
                                            @else
                                                <span class="text-success">FREE</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5 bg-light rounded-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3 opacity-50"></i>
                                <p class="text-muted mb-0">Belum ada event yang diselenggarakan.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- KOLOM KANAN: Info Organizer (Desktop Only) --}}
            <div class="col-lg-4 d-none d-lg-block">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px; z-index: 10;">
                    <div class="card-body p-4">
                        {{-- Logo & Name --}}
                        <div class="d-flex align-items-center mb-4">
                            <img src="{{ $organizer->organizer_logo_src }}" 
                                 alt="{{ $organizer->organizer_name }}" 
                                 class="rounded-3 me-3" width="60" height="60" style="object-fit: cover;">
                            <div>
                                <h6 class="fw-bold mb-0">{{ $organizer->organizer_name ?? $organizer->name }}</h6>
                                @if($organizer->organizer_name)
                                    <small class="text-muted">{{ $organizer->name }}</small>
                                @endif
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="border-top border-bottom py-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Events</span>
                                <span class="fw-bold text-primary-custom">{{ $events->count() }}</span>
                            </div>
                        </div>

                        {{-- Contact Info --}}
                        <div class="vstack gap-2">
                            @if($organizer->organizer_email)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="fas fa-envelope me-2"></i>Email</span>
                                    <span class="fw-semibold text-end" style="font-size: 0.85rem;">{{ $organizer->organizer_email }}</span>
                                </div>
                            @endif
                            
                            @if($organizer->organizer_phone)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="fas fa-phone me-2"></i>Phone</span>
                                    <span class="fw-semibold">{{ $organizer->organizer_phone }}</span>
                                </div>
                            @endif
                            
                            @if($organizer->organizer_city)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>City</span>
                                    <span class="fw-semibold">{{ $organizer->organizer_city }}</span>
                                </div>
                            @endif
                            
                            @if($organizer->organizer_instagram)
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="fab fa-instagram me-2"></i>Instagram</span>
                                    <a href="{{ $organizer->organizer_instagram }}" target="_blank" class="fw-semibold text-decoration-none text-primary-custom">
                                        <i class="fas fa-external-link-alt me-1"></i> Follow
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Description --}}
                        @if($organizer->organizer_description)
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="fw-bold mb-2">Tentang Organizer</h6>
                                <p class="text-muted small mb-0" style="line-height: 1.6;">
                                    {{ $organizer->organizer_description }}
                                </p>
                            </div>
                        @endif

                        {{-- Contact Button --}}
                        @if($organizer->organizer_phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $organizer->organizer_phone) }}" 
                               target="_blank" 
                               class="btn btn-success w-100 mt-4 py-2 fw-bold">
                                <i class="fab fa-whatsapp me-2"></i> Contact Now
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ========================================= */
        /* ORGANIZER HEADER - Pockets.id Style */
        /* =========================================  */
        .organizer-header {
            @if(isset($globalSettings['organizer_banner_path']) && $globalSettings['organizer_banner_path'])
                background-image: url('{{ asset('storage/' . $globalSettings['organizer_banner_path']) }}');
                background-size: cover;
                background-position: center;
            @else
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            @endif
            position: relative;
            padding: 2rem 0;
            margin-bottom: 0;
            margin-left: -12px;
            margin-right: -12px;
            margin-top: -0.5rem;
            overflow: hidden; /* Prevent shadow leaking */
            z-index: 100; /* Keep header above sticky sidebar */
        }
        
        }
        
        .organizer-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.4); /* Darker overlay for better text readability on image */
            border-radius: inherit; /* Match parent border-radius */
        }
        
        .organizer-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        /* Logo Card Style */
        .organizer-logo-card {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            min-width: 90px;
        }
        
        .organizer-logo-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 6px;
        }
        
        .organizer-logo-label {
            display: block;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        /* Organizer Info */
        .organizer-info {
            flex: 1;
        }
        
        .organizer-name {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0;
            line-height: 1.2;
        }
        
        .organizer-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .organizer-member-since {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            margin-bottom: 0;
        }
        
        /* Follow Button */
        .organizer-follow {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .follow-text {
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .follow-btn {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .follow-btn:hover {
            background: white;
            color: var(--primary-color);
        }
        
        /* Content Area */
        .organizer-content {
            margin-top: 1.5rem;
        }
        
        /* Mobile Info Card */
        .organizer-info-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-of-type {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        /* ========================================= */
        /* EVENT CARDS - Match Homepage Style */
        /* ========================================= */
        .card-event {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-event:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
        }
        
        /* Event Card Image - Same as Homepage */
        .event-card-img {
            width: 100%;
            aspect-ratio: 3/2; /* 600x400 ratio */
            object-fit: cover;
            height: auto;
        }
        
        /* Date Strip Style */
        .date-strip {
            font-size: 0.75rem;
        }
        
        /* Event Price */
        .event-price {
            font-size: 0.85rem;
        }
        
        /* Mobile Compact Styles */
        @media (max-width: 767.98px) {
            .event-card-img {
                height: auto !important;
                aspect-ratio: 3/2; /* Same as original to prevent cropping */
                object-fit: cover;
            }
            
            .card-event .card-body {
                padding: 0.5rem !important;
            }
            .card-event .card-title {
                font-size: 0.85rem !important;
                margin-bottom: 0.2rem !important;
                line-height: 1.2;
            }
            .card-event .date-strip {
                font-size: 0.6rem !important;
                padding: 0.2rem 0.5rem !important;
            }
            .card-event .event-location {
                font-size: 0.7rem !important;
            }
            .card-event .event-price {
                font-size: 0.7rem !important;
            }
        }
        
        /* Card Body Mobile */
        .event-title {
            font-size: 0.85rem;
            line-height: 1.3;
        }
        
        .event-location {
            font-size: 0.75rem;
        }
        
        .event-price-row {
            font-size: 0.8rem;
        }
        
        /* ========================================= */
        /* RESPONSIVE STYLES */
        /* ========================================= */
        @media (max-width: 767.98px) {
            .organizer-header {
                padding: 1.5rem 0;
                min-height: 180px;
                background-size: cover;
                background-position: center;
            }
            
            .organizer-header-content {
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .organizer-logo-card {
                min-width: 75px;
                padding: 10px;
            }
            
            .organizer-logo-img {
                width: 40px;
                height: 40px;
            }
            
            .organizer-logo-label {
                font-size: 0.6rem;
            }
            
            .organizer-name {
                font-size: 1.4rem;
            }
            
            .organizer-subtitle,
            .organizer-member-since {
                font-size: 0.8rem;
            }
            
            .organizer-follow {
                width: 100%;
                justify-content: flex-end;
                margin-top: 0.5rem;
            }
            
            .follow-text {
                font-size: 0.85rem;
            }
            
            .follow-btn {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }
            
            /* Event Grid */
            .event-grid {
                margin-left: -6px;
                margin-right: -6px;
            }
            
            .event-grid > div {
                padding-left: 6px;
                padding-right: 6px;
            }
            
            .card-img-wrapper {
                height: 120px;
            }
            
            .event-title {
                font-size: 0.8rem;
            }
            
            .event-location {
                font-size: 0.7rem;
            }
            
            .event-price-row {
                font-size: 0.75rem;
            }
        }
        
        /* Desktop Styles */
        @media (min-width: 992px) {
            .organizer-header {
                padding: 3rem 0;
                margin-left: 0;
                margin-right: 0;
                margin-top: 0;
                border-radius: 0 0 24px 24px;
            }
            
            .organizer-logo-card {
                min-width: 120px;
                padding: 16px;
            }
            
            .organizer-logo-img {
                width: 60px;
                height: 60px;
            }
            
            .organizer-logo-label {
                font-size: 0.75rem;
            }
            
            .organizer-name {
                font-size: 2.2rem;
            }
            
            .card-img-wrapper {
                height: 180px;
            }
            
            .event-title {
                font-size: 0.95rem;
            }
            
            .event-date-item {
                font-size: 0.75rem;
            }
        }
    </style>
@endsection
