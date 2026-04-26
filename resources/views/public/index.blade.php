@extends('layouts.public')

@section('title', 'Jelajahi Event')

@section('hero')
    @if(request('view') != 'all')
    <div class="container pt-2 pt-md-4">
        {{-- HERO SLIDER (CAROUSEL) --}}
        <div id="heroCarousel" class="carousel slide carousel-fade shadow rounded-4 overflow-hidden" data-bs-ride="carousel" data-bs-interval="4000">
            <div class="carousel-inner">
                @forelse($banners as $key => $banner)
                    {{-- Tinggi Banner Responsive (CSS defined in layout: 160px Mob / 500px Desk) --}}
                    <div class="carousel-item {{ $key == 0 ? 'active' : '' }} hero-banner-item">
                        <div class="position-relative w-100 h-100">
                            {{-- Link Pembungkus Gambar (Jika ada URL) --}}
                            @php
                                $bannerContent = '
                                    <picture class="d-block w-100 h-100">
                                        <source media="(min-width: 768px)" srcset="' . $banner->banner_src . '">
                                        <img src="' . $banner->mobile_banner_src . '" 
                                             class="d-block w-100 h-100" 
                                             style="object-fit: cover;"
                                             alt="' . ($banner->title ?? 'Banner') . '">
                                    </picture>
                                ';
                            @endphp

                            @if($banner->url)
                                <a href="{{ $banner->url }}" class="d-block w-100 h-100">
                                    {!! $bannerContent !!}
                                </a>
                            @else
                                {!! $bannerContent !!}
                            @endif
                        </div>
                    </div>
                @empty
                    {{-- Fallback Slide jika belum ada banner --}}
                    <div class="carousel-item active hero-banner-item">
                        <img src="https://via.placeholder.com/1300x500?text=Welcome+to+TixKita" class="d-block w-100 h-100" style="object-fit: cover;">
                    </div>
                @endforelse
            </div>
            
            {{-- Tombol Navigasi Kiri/Kanan --}}
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon p-3 bg-dark bg-opacity-25 rounded-circle" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon p-3 bg-dark bg-opacity-25 rounded-circle" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    @endif
@endsection

@section('content')
    
    {{-- CATEGORY FILTER PILLS (Pockets.id Style) --}}
    @if(isset($categories) && $categories->count() > 0)
    <div class="category-filter-section mb-1 mt-2">
        <div class="d-flex flex-nowrap overflow-auto gap-2 py-2 hide-scrollbar">
            {{-- All Button --}}
            <a href="{{ route('public.index', array_merge(request()->except('category'), ['category' => ''])) }}" 
               class="btn {{ empty($activeCategory) ? 'btn-primary-custom' : 'btn-outline-secondary' }} rounded-pill px-4 py-2 text-nowrap flex-shrink-0 category-pill">
                All
            </a>
            
            {{-- FREE Button (Auto: Events with all 0 price) --}}
            <a href="{{ route('public.index', array_merge(request()->except('category'), ['category' => 'free'])) }}" 
               class="btn {{ $activeCategory === 'free' ? 'btn-success' : 'btn-outline-success' }} rounded-pill px-4 py-2 text-nowrap flex-shrink-0 category-pill">
                <i class="fas fa-gift me-1"></i> Free
            </a>
            
            {{-- Category Pills --}}
            @foreach($categories as $category)
                <a href="{{ route('public.index', array_merge(request()->except('category'), ['category' => $category->id])) }}" 
                   class="btn {{ $activeCategory == $category->id ? 'btn-primary-custom' : 'btn-outline-secondary' }} rounded-pill px-4 py-2 text-nowrap flex-shrink-0 category-pill">
                    @if($category->icon)
                        <i class="fas {{ $category->icon }} me-1"></i>
                    @endif
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
    
    <style>
        .category-filter-section {
            margin-left: -0.75rem;
            margin-right: -0.75rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .category-pill {
            font-weight: 500;
            font-size: 0.875rem;
            border-width: 2px;
            transition: all 0.2s ease;
        }
        .category-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .category-pill.btn-outline-secondary {
            border-color: #dee2e6;
            color: #333;
            background-color: #fff;
        }
        .category-pill.btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
    </style>
    @endif


    <style>
        /* Event Card Image Responsive */
        .event-card-img {
            /* height: 160px; REMOVED fixed height */
            width: 100%;
            aspect-ratio: 3/2; /* 600x400 ratio */
            object-fit: cover;
            height: auto;
        }
        
        /* Mobile Compact Styles */
        @media (max-width: 767.98px) {
            .event-card-img {
                height: auto !important;
                aspect-ratio: 3/2; /* Same as original to prevent cropping */
                object-fit: cover;
            }
            /* Widen negative margin for row to fit screen better if needed, or just rely on g-2 */
            
            .card-event .card-body {
                padding: 0.5rem !important; /* Tighter padding */
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
                font-size: 0.7rem !important; /* Mobile: Smaller price */
            }
        }
        
        /* Desktop Price Size */
        .event-price {
            font-size: 0.85rem;
        }
    </style>



    {{-- Header Daftar Event --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0">
            @if(request('view') == 'all')
                Semua Event
            @elseif(request('q'))
                Hasil Pencarian: "{{ request('q') }}"
            @else
                Event Pilihan & Terlaris
            @endif
        </h5>
        
        @if(request('view') != 'all')
            <a href="{{ route('public.index', ['view' => 'all']) }}" class="text-decoration-none fw-bold small text-primary-custom">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
        @else
             <a href="{{ route('public.index') }}" class="text-decoration-none fw-bold small text-secondary">Kembali <i class="fas fa-times ms-1"></i></a>
        @endif
    </div>

    {{-- Grid Event List --}}
    <div class="row g-2" id="event-list">
        @forelse ($events as $event)
            <div class="col-6 col-md-3 mb-3 event-item" 
                 data-location-id="{{ $event->location_id }}"
                 data-date="{{ \Carbon\Carbon::parse($event->tgl_mulai)->format('Y-m-d') }}">
                
                {{-- Card Event --}}
                <div class="card card-event border-0 shadow-sm rounded-3 overflow-hidden">
                    <a href="{{ route('public.event.detail', $event->event_id) }}" class="text-decoration-none text-dark">
                        <div class="position-relative">
                            {{-- Menggunakan Accessor 'card_src' (Gambar Kecil) --}}
                            <img src="{{ $event->card_src }}" 
                                 alt="{{ $event->judul }}"
                                 class="w-100 event-card-img"
                                 loading="lazy"
                            >
                            {{-- Badge Status --}}
                            <span class="position-absolute top-0 end-0 m-1 badge bg-white text-dark shadow-sm rounded-pill px-2 py-1 small fw-bold" style="font-size: 0.5rem;">
                                {{ $event->status }}
                            </span>
                        </div>
                        
                        {{-- Date Strip (Pockets Style) --}}
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
                            {{-- Lokasi --}}
                            <div class="small text-muted mb-2 text-truncate event-location">
                                <i class="fas fa-map-marker-alt me-1 text-secondary-custom"></i> 
                                {{ $event->location->kota ?? 'Online' }}
                            </div>
                            
                            {{-- Harga (Kanan Bawah) --}}
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
            <div class="col-12 py-5 text-center">
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-state-2130362-1800926.png" alt="Empty" style="width: 150px; opacity: 0.5">
                @if(request('q'))
                    <p class="text-muted mt-3">Tidak ditemukan event dengan kata kunci "<strong>{{ request('q') }}</strong>".</p>
                    <a href="{{ route('public.index') }}" class="btn btn-outline-primary-custom mt-2">Reset Pencarian</a>
                @else
                    <p class="text-muted mt-3">Belum ada acara yang tersedia saat ini.</p>
                @endif
            </div>
        @endforelse
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Inisialisasi Manual Carousel (Agar auto-slide lebih stabil)
        var myCarousel = document.querySelector('#heroCarousel')
        if(myCarousel) {
            var carousel = new bootstrap.Carousel(myCarousel, {
                interval: 4000, 
                ride: 'carousel',
                wrap: true
            });
        }
    });
</script>
@endsection