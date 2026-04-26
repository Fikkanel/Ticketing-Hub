@extends('layouts.public')

@section('title', $event->judul)

@section('content')
    {{-- Breadcrumb Navigasi --}}
    <div class="row">
        <div class="col-12 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="{{ route('public.index') }}" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $event->judul }}</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- ORGANIZER BADGE --}}
    @if($event->primary_organizer && $event->primary_organizer->hasOrganizerProfile())
        <div class="organizer-badge mb-3">
            <a href="{{ route('public.organizer.profile', $event->primary_organizer->organizer_slug) }}" 
               class="d-inline-flex align-items-center text-decoration-none text-dark bg-light rounded-pill px-3 py-2 shadow-sm hover-lift">
                <img src="{{ $event->primary_organizer->organizer_logo_src }}" 
                     class="rounded-circle me-2" 
                     width="32" height="32" 
                     style="object-fit: cover;"
                     alt="{{ $event->primary_organizer->organizer_name }}">
                <span class="fw-semibold">{{ $event->primary_organizer->organizer_name }}</span>
                <i class="fas fa-chevron-right ms-2 text-muted small"></i>
            </a>
        </div>
    @endif

    {{-- BANNER UTAMA (Responsive) --}}
    <div class="rounded-4 overflow-hidden mb-4 shadow-sm position-relative event-detail-banner">
        <img src="{{ $event->banner_src }}" 
             class="w-100 h-100 object-fit-cover" 
             style="object-position: center;"
             alt="Banner {{ $event->judul }}">
    </div>

    {{-- HEADER INFO (Judul & Tanggal dipindah ke sini karena banner sudah bersih) --}}
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold text-dark mb-2">{{ $event->judul }}</h1>
            <div class="d-flex flex-wrap gap-4 text-muted small align-items-center">


                <div class="d-flex align-items-center">
                    <i class="fas fa-map-marker-alt me-2 text-secondary-custom"></i>
                    <span class="fw-semibold">{{ $event->location->nama_lokasi ?? 'Lokasi Online' }} ({{ $event->location->kota ?? '-' }})</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fas fa-calendar-alt me-2 text-primary-custom"></i>
                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($event->tgl_mulai)->format('d F Y, H:i') }} WIB</span>
                </div>
                <div>
                    <span class="badge {{ $event->status == 'Active' ? 'bg-success' : 'bg-secondary' }} px-3 rounded-pill">
                        {{ $event->status }}
                    </span>
                </div>
            </div>


        </div>
    </div>

    <div class="row">
        {{-- KOLOM KIRI: Deskripsi & Info --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Tentang Event Ini</h5>
                    <p class="text-muted" style="line-height: 1.8; white-space: pre-line;">
                        {{ $event->deskripsi }}
                    </p>

                    {{-- GOOGLE MAPS IFRAME --}}
                    @if(isset($event->location->map_link) && !empty($event->location->map_link))
                        <div class="mt-4 rounded-4 overflow-hidden shadow-sm border">
                            <iframe src="{{ $event->location->map_link }}" width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    @endif
                    
                    <hr class="my-4 border-dashed">
                    
                    {{-- VENUE MAP (If Manual Map) --}}
                    @if($event->layout_type === 'manual_map' && $event->venueMap)
                        <h5 class="fw-bold mb-3">Peta Venue</h5>
                        <div class="rounded-4 overflow-hidden shadow-sm border" style="background-color: #f8f9fa; position: relative; width: 100%; min-height: 400px;" id="venue-panzoom-parent">
                            <div class="position-absolute top-0 end-0 p-3" style="z-index: 10;">
                                <button type="button" class="btn btn-sm btn-light border shadow-sm" id="btn-venue-zoom-out" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                                <button type="button" class="btn btn-sm btn-light border shadow-sm" id="btn-venue-zoom-reset" title="Reset Zoom"><i class="fas fa-compress"></i></button>
                                <button type="button" class="btn btn-sm btn-light border shadow-sm" id="btn-venue-zoom-in" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                            </div>
                            <div id="public-venue-map-container" class="d-flex justify-content-center align-items-center h-100 p-2">
                                <canvas id="public-venue-canvas"></canvas>
                            </div>
                        </div>
                        <p class="text-muted small mt-2 mb-4"><i class="fas fa-info-circle me-1"></i> Klik pada area berwarna di peta untuk memilih tiket.</p>
                        <hr class="my-4 border-dashed">
                    @endif

                    <h5 class="fw-bold mb-3">Syarat & Ketentuan</h5>
                    <ul class="text-muted small ps-3 mb-0" style="line-height: 1.6;">
                        <li>E-Ticket yang sudah dibeli tidak dapat ditukar atau dikembalikan (non-refundable).</li>
                        <li>Harap membawa kartu identitas yang berlaku saat penukaran tiket fisik.</li>
                        <li>Dilarang membawa senjata tajam, obat-obatan terlarang, dan hewan peliharaan.</li>
                        <li>Panitia berhak menolak pengunjung yang tidak mematuhi protokol keamanan.</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Sticky Sidebar Pembelian --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow rounded-4 sticky-top" style="top: 100px; z-index: 5;">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold">Pilih Tiket & Kit</h5>
                    <p class="text-muted small">Silakan pilih kategori tiket atau merchandise yang tersedia.</p>
                </div>
                
                <div class="card-body px-4 pb-4">
                    <div class="vstack gap-3">
                        @forelse ($event->products as $product)
                            <div class="border rounded-3 p-3 position-relative bg-light hover-shadow transition-all">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $product->nama_produk }}</h6>
                                        <span class="badge {{ $product->tipe == 'Fisik' ? 'bg-info' : 'bg-warning' }} text-dark rounded-pill" style="font-size: 10px;">
                                            {{ $product->tipe }}
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary-custom">Rp {{ number_format($product->harga, 0, ',', '.') }}</div>
                                        <small class="text-muted" style="font-size: 10px;">/ pax</small>
                                    </div>
                                </div>
                                
                                <p class="small text-muted mb-3" style="font-size: 0.85rem;">
                                    {{ Str::limit($product->deskripsi, 60) }}
                                </p>
                                
                                {{-- TOMBOL ADD TO CART --}}
                                {{-- TOMBOL ADD TO CART / SOLD OUT --}}
                                @if($product->stok > 0)
                                    @if($product->kategori_tiket === 'seating')
                                        <button class="btn btn-outline-primary-custom w-100 btn-sm fw-bold" 
                                                onclick="openSeatSelectionModal('{{ $product->product_id }}'); return false;">
                                            <i class="fas fa-chair me-1"></i> Pilih Kursi
                                        </button>
                                    @else
                                        <button class="btn btn-outline-primary-custom w-100 btn-sm fw-bold" 
                                                onclick="addProductToCart('{{ $product->product_id }}', '{{ addslashes($product->nama_produk) }}', {{ $product->harga }}, {{ $product->stok }}); return false;">
                                            <i class="fas fa-plus me-1"></i> Tambah ke Keranjang
                                        </button>
                                    @endif
                                @else
                                    <button class="btn btn-secondary w-100 btn-sm fw-bold" disabled>
                                        <i class="fas fa-ban me-1"></i> Sold Out
                                    </button>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-4 bg-light rounded-3">
                                <i class="fas fa-box-open fa-2x text-muted mb-2 opacity-50"></i>
                                <p class="small text-muted mb-0">Tiket/Produk belum tersedia untuk event ini.</p>
                            </div>
                        @endforelse
                        
                        {{-- BUNDLE SECTION --}}
                        @if($event->bundles->where('is_active', true)->count() > 0)
                            <hr class="my-3 border-dashed">
                            <h6 class="fw-bold text-danger mb-3">
                                <i class="fas fa-gift me-2"></i>Paket Hemat
                            </h6>
                            
                            @foreach($event->bundles->where('is_active', true) as $bundle)
                                @php
                                    // Check if bundle has stock AND all products have sufficient stock
                                    $bundleAvailable = $bundle->stok > 0;
                                    $insufficientProducts = [];
                                    
                                    foreach ($bundle->items as $bundleItem) {
                                        $product = $bundleItem->product;
                                        if ($product && $product->stok < $bundleItem->quantity) {
                                            $bundleAvailable = false;
                                            $insufficientProducts[] = $product->nama_produk . ' (tersedia: ' . $product->stok . ', butuh: ' . $bundleItem->quantity . ')';
                                        }
                                    }
                                @endphp
                                
                                <div class="border border-danger rounded-3 p-3 position-relative bg-danger bg-opacity-10 {{ !$bundleAvailable ? 'opacity-75' : '' }}">
                                    {{-- Discount Badge --}}
                                    @if($bundle->discount_percentage > 0)
                                        <span class="position-absolute top-0 end-0 translate-middle badge bg-danger">
                                            -{{ $bundle->discount_percentage }}%
                                        </span>
                                    @endif
                                    
                                    @if(!$bundleAvailable)
                                        <span class="badge bg-secondary position-absolute" style="top: 8px; left: 8px;">
                                            SOLD OUT
                                        </span>
                                    @endif
                                    
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $bundle->name }}</h6>
                                            <span class="badge bg-danger text-white rounded-pill" style="font-size: 10px;">
                                                BUNDLE
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-decoration-line-through text-muted small">
                                                Rp {{ number_format($bundle->total_value, 0, ',', '.') }}
                                            </div>
                                            <div class="fw-bold text-danger fs-5">
                                                Rp {{ number_format($bundle->price, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($bundle->description)
                                        <p class="small text-muted mb-2" style="font-size: 0.85rem;">
                                            {{ $bundle->description }}
                                        </p>
                                    @endif
                                    
                                    {{-- Bundle Contents --}}
                                    <div class="small mb-3">
                                        <strong class="text-dark">Isi:</strong>
                                        @foreach($bundle->items as $item)
                                            <span class="badge bg-light text-dark border ms-1">
                                                {{ $item->product->nama_produk ?? '?' }} x{{ $item->quantity }}
                                            </span>
                                        @endforeach
                                    </div>
                                    
                                    @if($bundleAvailable)
                                        <button class="btn btn-danger w-100 btn-sm fw-bold add-bundle-to-cart" 
                                                data-bundle-id="{{ $bundle->id }}" 
                                                data-name="{{ $bundle->name }} (Bundle)"
                                                data-price="{{ $bundle->price }}"
                                                data-stock="{{ $bundle->stok }}"
                                                data-items="{{ json_encode($bundle->items->map(fn($i) => ['product_id' => $i->product_id, 'qty' => $i->quantity])) }}">
                                            <i class="fas fa-plus me-1"></i> Tambah Paket
                                        </button>
                                    @else
                                        <button class="btn btn-secondary w-100 btn-sm fw-bold" disabled>
                                            <i class="fas fa-times me-1"></i> Stok Habis
                                        </button>
                                        @if(count($insufficientProducts) > 0)
                                            <small class="text-danger d-block mt-1">
                                                <i class="fas fa-exclamation-circle"></i> 
                                                {{ implode(', ', $insufficientProducts) }}
                                            </small>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="card-footer bg-white p-3 border-top-0">
                    <a href="{{ route('public.cart.show') }}" class="btn btn-primary-custom w-100 py-2 shadow-sm fw-bold">
                        <i class="fas fa-shopping-cart me-2"></i> Lihat Keranjang
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PILIH KURSI --}}
    <div class="modal fade" id="seatSelectionModal" tabindex="-1" aria-labelledby="seatSelectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0 pb-3">
                    <h5 class="modal-title fw-bold" id="seatSelectionModalLabel">Pilih Kursi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-lg-9 bg-light border-end position-relative" style="min-height: 400px; overflow: hidden;" id="public-panzoom-parent">
                            <div class="position-absolute top-0 end-0 p-3" style="z-index: 10;">
                                <button type="button" class="btn btn-sm btn-light border shadow-sm" id="btn-public-zoom-out" title="Zoom Out"><i class="fas fa-search-minus"></i></button>
                                <button type="button" class="btn btn-sm btn-light border shadow-sm" id="btn-public-zoom-reset" title="Reset Zoom"><i class="fas fa-compress"></i></button>
                                <button type="button" class="btn btn-sm btn-light border shadow-sm" id="btn-public-zoom-in" title="Zoom In"><i class="fas fa-search-plus"></i></button>
                            </div>
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div id="public-seat-layout-container" class="position-relative p-4 bg-white shadow-sm border rounded" style="width: max-content; margin: auto; transform-origin: center center;">
                                    {{-- Layout generated by JS --}}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 bg-white">
                            <div class="p-4 d-flex flex-column h-100">
                                <h6 class="fw-bold mb-3" id="modal-product-name">Produk</h6>
                                <div class="mb-4 text-muted small">
                                    <div class="d-flex align-items-center mb-2">
                                        <div style="width:15px;height:15px;background-color:#0d6efd;border-radius:3px;" class="me-2"></div>
                                        <span>Kursi Anda</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div style="width:15px;height:15px;background-color:#e9ecef;border-radius:3px;border:1px solid #ccc" class="me-2"></div>
                                        <span>Tersedia</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div style="width:15px;height:15px;background-color:#6c757d;border-radius:3px;" class="me-2"></div>
                                        <span>Terjual/Reserved</span>
                                    </div>
                                </div>

                                <div class="flex-grow-1">
                                    <h6 class="fw-bold small">Kursi Terpilih (<span id="selected-seats-count">0</span>):</h6>
                                    <div id="selected-seats-list" class="d-flex flex-wrap gap-1 mb-3">
                                        <span class="text-muted small">Belum ada kursi yang dipilih.</span>
                                    </div>
                                </div>

                                <div class="mt-auto pt-3 border-top">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="fw-bold">Total:</span>
                                        <span class="fw-bold text-primary-custom" id="selected-seats-total">Rp 0</span>
                                    </div>
                                    <button class="btn btn-primary-custom w-100 fw-bold" id="btn-confirm-seats" disabled>
                                        <i class="fas fa-shopping-cart me-2"></i> Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Event Detail Banner Responsive */
        .event-detail-banner {
            width: 100%;
            max-width: 1320px;
            aspect-ratio: 1320/300;
            height: auto !important;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }

        .event-detail-banner img {
            width: 100%;
            height: 100%;
            object-fit: contain; /* Agar gambar tidak terpotong, tapi fit dalam kotak rasio */
        }
        
        @media (max-width: 767.98px) {
            .event-detail-banner {
                 width: 100%;
                 /* Aspect ratio inherited */
            }
        }

        /* Organizer Badge Hover */
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }

        /* Stage Shapes */
        .stage.top.convex { border-radius: 0 0 50% 50% / 0 0 20px 20px; }
        .stage.top.concave { border-radius: 50% 50% 0 0 / 20px 20px 0 0; }
        
        .stage.bottom.convex { border-radius: 50% 50% 0 0 / 20px 20px 0 0; }
        .stage.bottom.concave { border-radius: 0 0 50% 50% / 0 0 20px 20px; }
        
        .stage.left.convex { border-radius: 0 50% 50% 0 / 0 20px 20px 0; }
        .stage.left.concave { border-radius: 50% 0 0 50% / 20px 0 0 20px; }
        
        .stage.right.convex { border-radius: 50% 0 0 50% / 20px 0 0 20px; }
        .stage.right.concave { border-radius: 0 50% 50% 0 / 0 20px 20px 0; }

        /* Public Seat Styles */
        .public-seat-grid {
            display: grid;
            gap: 8px;
            margin: 20px;
        }
        .public-seat-cell {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .public-seat-cell.available {
            background-color: #e9ecef;
            color: #495057;
            border: 1px solid #ced4da;
        }
        .public-seat-cell.available:hover {
            background-color: #dee2e6;
            transform: scale(1.1);
        }
        .public-seat-cell.selected {
            background-color: #0d6efd;
            color: white;
            border-color: #0a58ca;
            transform: scale(1.1);
        }
        .public-seat-cell.booked {
            background-color: #6c757d;
            color: #dee2e6;
            cursor: not-allowed;
            box-shadow: none;
            opacity: 0.7;
        }
    </style>
    
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom/dist/panzoom.min.js"></script>
<script>
    const seatingProductsData = {
        @foreach($event->products->where('kategori_tiket', 'seating') as $prod)
            "{{ $prod->product_id }}": {
                name: "{{ $prod->nama_produk }}",
                price: {{ $prod->harga }},
                stock: {{ $prod->stok }},
                layout: {
                    rows: {{ $prod->seatLayout->rows ?? 10 }},
                    columns: {{ $prod->seatLayout->columns ?? 10 }},
                    stage_position: "{{ $prod->seatLayout->stage_position ?? 'top' }}",
                    stage_shape: "{{ $prod->seatLayout->stage_shape ?? 'normal' }}"
                },
                seats: [
                    @if($prod->seatLayout)
                        @foreach($prod->seatLayout->seats as $seat)
                        {
                            id: {{ $seat->id }},
                            row: {{ $seat->row_index }},
                            col: {{ $seat->col_index }},
                            label: "{{ $seat->seat_number }}",
                            is_active: {{ $seat->is_active ? 'true' : 'false' }},
                            is_booked: {{ $seat->isBooked() ? 'true' : 'false' }}
                        },
                        @endforeach
                    @endif
                ]
            },
        @endforeach
    };

    const eventProducts = {
        @foreach($event->products as $prod)
            "{{ $prod->product_id }}": {
                name: "{{ $prod->nama_produk }}",
                price: {{ $prod->harga }},
                stock: {{ $prod->stok }},
                type: "{{ $prod->kategori_tiket }}"
            },
        @endforeach
    };
    
    const venueMapData = {!! $event->venueMap && $event->layout_type === 'manual_map' ? json_encode($event->venueMap->canvas_data) : 'null' !!};
    const venueZonesData = {!! ($event->venueMap && $event->layout_type === 'manual_map') ? json_encode($event->venueMap->zones) : '[]' !!};
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Notification helper
    function notify(type, message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        } else {
            alert(message);
        }
    }

    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // ============================================================
    // ADD PRODUCT TO CART (Server-Side API)
    // ============================================================
    let productAddProcessing = false;
    window.addProductToCart = async function(productId, name, price, stock, seatIds = []) {
        if (productAddProcessing) return;
        productAddProcessing = true;

        try {
            const bodyData = {
                id: String(productId),
                qty: seatIds.length > 0 ? seatIds.length : 1
            };
            
            if (seatIds.length > 0) {
                bodyData.seat_ids = seatIds;
            }

            const response = await fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(bodyData)
            });

            const result = await response.json();

            if (response.ok) {
                // Update cart badge
                if (typeof updateCartIcon === 'function') updateCartIcon();

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Ditambahkan!',
                    html: `<p><strong>${name}</strong> telah masuk ke keranjang belanja Anda.</p>`,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-shopping-cart me-1"></i> Lihat Keranjang',
                    cancelButtonText: 'Lanjut Belanja',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.href = '{{ route("public.cart.show") }}';
                    }
                });
            } else {
                notify('error', result.message || 'Gagal menambahkan item.');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            notify('error', 'Gagal terhubung ke server.');
        }

        setTimeout(() => { productAddProcessing = false; }, 500);
    };

    // ============================================================
    // SEAT SELECTION MODAL LOGIC
    // ============================================================
    let currentSeatingProductId = null;
    let currentSelectedSeats = [];
    const seatSelectionModalEl = document.getElementById('seatSelectionModal');
    let seatModal;
    
    if (seatSelectionModalEl) {
        seatModal = new bootstrap.Modal(seatSelectionModalEl);
    }

    window.openSeatSelectionModal = function(productId) {
        if (!seatingProductsData[productId]) return;
        
        currentSeatingProductId = productId;
        currentSelectedSeats = [];
        const prodData = seatingProductsData[productId];
        
        document.getElementById('modal-product-name').innerText = prodData.name;
        updateSelectedSeatsUI();
        renderPublicSeatLayout(prodData);
        
        // Initialize Panzoom
        if (!window.publicPanzoomInstance) {
            const container = document.getElementById('public-seat-layout-container');
            window.publicPanzoomInstance = Panzoom(container, {
                maxScale: 3,
                minScale: 0.1,
                step: 0.2,
                cursor: 'grab'
            });

            const parent = document.getElementById('public-panzoom-parent');
            parent.addEventListener('wheel', window.publicPanzoomInstance.zoomWithWheel);

            document.getElementById('btn-public-zoom-in').addEventListener('click', window.publicPanzoomInstance.zoomIn);
            document.getElementById('btn-public-zoom-out').addEventListener('click', window.publicPanzoomInstance.zoomOut);
            document.getElementById('btn-public-zoom-reset').addEventListener('click', window.publicPanzoomInstance.reset);
        }
        
        window.publicPanzoomInstance.reset();
        
        seatModal.show();
    };

    // ============================================================
    // VENUE MAP LOGIC
    // ============================================================
    if (document.getElementById('public-venue-canvas')) {
        const venueCanvas = new fabric.Canvas('public-venue-canvas', {
            selection: false,
            hoverCursor: 'pointer',
            width: document.getElementById('public-venue-map-container').clientWidth || 800,
            height: document.getElementById('public-venue-map-container').clientHeight || 600
        });

        // Load Zones
        if (venueZonesData && venueZonesData.length > 0) {
            venueZonesData.forEach(zone => {
                try {
                    const coords = typeof zone.coordinates === 'string' ? JSON.parse(zone.coordinates) : zone.coordinates;
                    const rect = new fabric.Rect({
                        left: 0, top: 0,
                        width: coords.width,
                        height: coords.height,
                        fill: coords.fill,
                        opacity: 0.8,
                        originX: 'center',
                        originY: 'center',
                        rx: 5, ry: 5
                    });

                    const text = new fabric.Text(zone.name, {
                        left: 0, top: 0,
                        fontSize: 18,
                        fontWeight: 'bold',
                        fill: '#fff',
                        originX: 'center',
                        originY: 'center',
                        shadow: new fabric.Shadow({ color: 'rgba(0,0,0,0.5)', blur: 2, offsetX: 1, offsetY: 1 })
                    });

                    // Add price text below name if product exists
                    const prod = eventProducts[zone.product_id];
                    const priceStr = prod ? `Rp ${prod.price.toLocaleString('id-ID')}` : '';
                    
                    const priceText = new fabric.Text(priceStr, {
                        left: 0, top: 0,
                        fontSize: 14,
                        fill: '#fff',
                        originX: 'center',
                        originY: 'center',
                        top: 15, // Offset relative to group center
                        shadow: new fabric.Shadow({ color: 'rgba(0,0,0,0.5)', blur: 2, offsetX: 1, offsetY: 1 })
                    });
                    
                    // Adjust text position slightly up to make room for price
                    text.set('top', -10);

                    const group = new fabric.Group([rect, text, priceText], {
                        left: coords.left,
                        top: coords.top,
                        angle: coords.angle,
                        originX: 'center',
                        originY: 'center',
                        hasControls: false,
                        hasBorders: false,
                        selectable: false,
                        hoverCursor: 'pointer'
                    });

                    group.set('zoneData', {
                        id: zone.id,
                        name: zone.name,
                        productId: zone.product_id,
                        type: zone.type
                    });

                    // Hover effects
                    group.on('mouseover', function() {
                        rect.set('opacity', 1);
                        rect.set('stroke', '#fff');
                        rect.set('strokeWidth', 2);
                        venueCanvas.renderAll();
                    });
                    group.on('mouseout', function() {
                        rect.set('opacity', 0.8);
                        rect.set('strokeWidth', 0);
                        venueCanvas.renderAll();
                    });

                    // Click event
                    group.on('mousedown', function() {
                        const product = eventProducts[this.zoneData.productId];
                        if (!product) {
                            notify('error', 'Produk untuk zona ini tidak ditemukan.');
                            return;
                        }
                        
                        if (product.stock <= 0) {
                            notify('error', 'Tiket untuk zona ini sudah Sold Out.');
                            return;
                        }

                        if (this.zoneData.type === 'seating') {
                            openSeatSelectionModal(this.zoneData.productId);
                        } else {
                            addProductToCart(this.zoneData.productId, product.name, product.price, product.stock);
                        }
                    });

                    venueCanvas.add(group);
                } catch (e) {
                    console.error("Error drawing zone", e);
                }
            });
        }
        
        // Setup Panzoom for Venue Map
        const venueMapContainer = document.getElementById('public-venue-map-container');
        const venuePanzoomInstance = Panzoom(venueMapContainer, {
            maxScale: 3,
            minScale: 0.5,
            step: 0.2,
            cursor: 'grab'
        });

        const venueParent = document.getElementById('venue-panzoom-parent');
        venueParent.addEventListener('wheel', venuePanzoomInstance.zoomWithWheel);

        document.getElementById('btn-venue-zoom-in').addEventListener('click', venuePanzoomInstance.zoomIn);
        document.getElementById('btn-venue-zoom-out').addEventListener('click', venuePanzoomInstance.zoomOut);
        document.getElementById('btn-venue-zoom-reset').addEventListener('click', venuePanzoomInstance.reset);
    }

    function renderPublicSeatLayout(prodData) {
        const container = document.getElementById('public-seat-layout-container');
        container.innerHTML = '';
        
        const rows = prodData.layout.rows;
        const columns = prodData.layout.columns;
        const stagePos = prodData.layout.stage_position;
        const stageShape = prodData.layout.stage_shape;

        // Add stage
        const stage = document.createElement('div');
        stage.className = `stage ${stagePos} ${stageShape}`;
        stage.innerText = 'PANGGUNG';
        container.appendChild(stage);

        // Add grid
        const grid = document.createElement('div');
        grid.className = 'public-seat-grid';
        grid.style.gridTemplateColumns = `repeat(${columns}, 40px)`;
        grid.style.gridTemplateRows = `repeat(${rows}, 40px)`;
        
        grid.style.marginTop = stagePos === 'top' ? '40px' : '20px';
        grid.style.marginBottom = stagePos === 'bottom' ? '40px' : '20px';
        grid.style.marginLeft = stagePos === 'left' ? '40px' : '20px';
        grid.style.marginRight = stagePos === 'right' ? '40px' : '20px';

        let facingClass = '';
        if(stagePos === 'top') facingClass = 'facing-top';
        if(stagePos === 'bottom') facingClass = 'facing-bottom';
        if(stagePos === 'left') facingClass = 'facing-left';
        if(stagePos === 'right') facingClass = 'facing-right';

        // Map seats for easy lookup
        const seatsMap = {};
        prodData.seats.forEach(s => {
            seatsMap[`${s.row}-${s.col}`] = s;
        });

        for (let r = 0; r < rows; r++) {
            for (let c = 0; c < columns; c++) {
                const seatData = seatsMap[`${r}-${c}`];
                if (!seatData || !seatData.is_active) {
                    // Empty space
                    const empty = document.createElement('div');
                    grid.appendChild(empty);
                    continue;
                }

                const seat = document.createElement('div');
                seat.innerText = seatData.label;
                seat.className = `public-seat-cell ${facingClass}`;
                
                if (seatData.is_booked) {
                    seat.classList.add('booked');
                    seat.title = "Sudah Dipesan";
                } else {
                    seat.classList.add('available');
                    seat.title = "Tersedia";
                    
                    seat.addEventListener('click', function() {
                        toggleSeatSelection(seatData.id, seatData.label, seat);
                    });
                }
                
                grid.appendChild(seat);
            }
        }
        
        container.appendChild(grid);
    }

    function toggleSeatSelection(seatId, label, element) {
        const idx = currentSelectedSeats.findIndex(s => s.id === seatId);
        
        if (idx > -1) {
            // Deselect
            currentSelectedSeats.splice(idx, 1);
            element.classList.remove('selected');
            element.classList.add('available');
        } else {
            // Check limit (e.g., max 10 per checkout)
            if (currentSelectedSeats.length >= 10) {
                notify('warning', 'Maksimal memilih 10 kursi sekaligus.');
                return;
            }
            // Select
            currentSelectedSeats.push({id: seatId, label: label});
            element.classList.remove('available');
            element.classList.add('selected');
        }
        
        updateSelectedSeatsUI();
    }

    function updateSelectedSeatsUI() {
        const listEl = document.getElementById('selected-seats-list');
        const countEl = document.getElementById('selected-seats-count');
        const totalEl = document.getElementById('selected-seats-total');
        const btnConfirm = document.getElementById('btn-confirm-seats');
        
        countEl.innerText = currentSelectedSeats.length;
        
        if (currentSelectedSeats.length === 0) {
            listEl.innerHTML = '<span class="text-muted small">Belum ada kursi yang dipilih.</span>';
            totalEl.innerText = 'Rp 0';
            btnConfirm.disabled = true;
        } else {
            listEl.innerHTML = '';
            currentSelectedSeats.forEach(s => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary';
                badge.innerText = s.label;
                listEl.appendChild(badge);
            });
            
            const prodData = seatingProductsData[currentSeatingProductId];
            const total = prodData.price * currentSelectedSeats.length;
            totalEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
            btnConfirm.disabled = false;
        }
    }

    document.getElementById('btn-confirm-seats').addEventListener('click', function() {
        if (!currentSeatingProductId || currentSelectedSeats.length === 0) return;
        
        const prodData = seatingProductsData[currentSeatingProductId];
        const seatIds = currentSelectedSeats.map(s => s.id);
        
        seatModal.hide();
        addProductToCart(currentSeatingProductId, prodData.name, prodData.price, prodData.stock, seatIds);
    });

    // ============================================================
    // ADD BUNDLE TO CART (Server-Side API)
    // ============================================================
    document.querySelectorAll('.add-bundle-to-cart').forEach(button => {
        let isProcessing = false;
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            if (isProcessing) return;
            isProcessing = true;

            const bundleId = this.dataset.bundleId;
            const bundleCartId = 'bundle_' + bundleId;
            const name = this.dataset.name;

            try {
                const response = await fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        id: bundleCartId,
                        qty: 1
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    if (typeof updateCartIcon === 'function') updateCartIcon();

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Ditambahkan!',
                        html: `<p><strong>${name}</strong> telah masuk ke keranjang belanja Anda.</p>`,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-shopping-cart me-1"></i> Lihat Keranjang',
                        cancelButtonText: 'Lanjut Belanja',
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true
                    }).then((res) => {
                        if (res.isConfirmed) {
                            window.location.href = '{{ route("public.cart.show") }}';
                        }
                    });
                } else {
                    notify('error', result.message || 'Gagal menambahkan paket.');

                    // If stock error, disable button
                    if (response.status === 400 && result.message?.includes('Stok')) {
                        this.disabled = true;
                        this.classList.remove('btn-danger');
                        this.classList.add('btn-secondary');
                        this.innerHTML = '<i class="fas fa-times me-1"></i> Stok Habis';
                    }
                }
            } catch (error) {
                console.error('Error adding bundle:', error);
                notify('error', 'Gagal terhubung ke server.');
            }

            isProcessing = false;
        });
    });
});
</script>
@endsection
@endsection