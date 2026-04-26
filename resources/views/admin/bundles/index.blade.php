@extends('layouts.admin')

@section('title', 'Bundle Tiket - ' . $event->judul)

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.events') }}">Events</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.events.edit', $event->event_id) }}">{{ $event->judul }}</a></li>
                <li class="breadcrumb-item active">Bundle Tiket</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-gift me-2"></i>Bundle Tiket
            </h4>
            <a href="{{ route('admin.bundles.create', $event) }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Bundle
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Bundle List --}}
    @if($bundles->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Belum ada bundle untuk event ini</h5>
                <p class="text-muted small">Bundle adalah paket tiket dengan harga diskon</p>
                <a href="{{ route('admin.bundles.create', $event) }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Buat Bundle Pertama
                </a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($bundles as $bundle)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pt-3 px-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $bundle->name }}</h5>
                                    <span class="badge {{ $bundle->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $bundle->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </div>
                                @if($bundle->discount_percentage > 0)
                                    <span class="badge bg-danger fs-6">
                                        -{{ $bundle->discount_percentage }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body px-3 pb-3">
                            @if($bundle->description)
                                <p class="text-muted small mb-3">{{ $bundle->description }}</p>
                            @endif
                            
                            <div class="mb-3">
                                <strong class="small text-muted d-block mb-2">Isi Bundle:</strong>
                                @foreach($bundle->items as $item)
                                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom">
                                        <span class="small">{{ $item->product->nama_produk ?? 'Produk Tidak Ditemukan' }}</span>
                                        <span class="badge bg-light text-dark">x{{ $item->quantity }}</span>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Harga Satuan:</span>
                                <span class="text-decoration-line-through text-muted">Rp {{ number_format($bundle->total_value, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted small">Harga Bundle:</span>
                                <span class="fw-bold text-success fs-5">Rp {{ number_format($bundle->price, 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">
                                    <i class="fas fa-cubes me-1"></i>Stok: {{ $bundle->stok }}
                                </span>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.bundles.edit', [$event, $bundle]) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.bundles.destroy', [$event, $bundle]) }}" method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus bundle ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
