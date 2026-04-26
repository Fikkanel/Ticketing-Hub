@extends('layouts.admin')

@section('title', 'Manajemen Produk')

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center bg-white gap-2">
            <h6 class="m-0 fw-bold text-primary">Daftar Produk & Travel Kit</h6>
            <div class="d-flex gap-2">
                @if($selectedEventId)
                    <a href="{{ route('admin.bundles.index', $selectedEventId) }}" class="btn btn-sm btn-outline-info shadow-sm">
                        <i class="fas fa-gift fa-sm me-1"></i> Kelola Bundle
                    </a>
                @endif
                <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary shadow-sm">
                    <i class="fas fa-plus fa-sm text-white-50 me-1"></i> Tambah Produk Baru
                </a>
            </div>
        </div>
        <div class="card-body">
            {{-- Filter Form --}}
            @if(Auth::user()->isSuperAdmin() || $events->count() > 1) 
            <div class="bg-light rounded-3 p-3 mb-4">
                <form method="GET" action="{{ route('admin.products.index') }}" class="d-flex align-items-center">
                    <label for="event_id" class="me-2 small fw-bold mb-0 text-nowrap">Filter Event:</label>
                    <select name="event_id" id="event_id" class="form-select form-select-sm" style="min-width: 200px;" onchange="this.form.submit()">
                        <option value="">
                            {{ Auth::user()->isSuperAdmin() ? '-- Semua Event --' : '-- Semua Event Saya --' }}
                        </option>
                        @foreach($events as $id => $judul)
                            <option value="{{ $id }}" {{ $selectedEventId == $id ? 'selected' : '' }}>
                                {{ Str::limit($judul, 30) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            @endif
            @if($products->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3 opacity-50"></i>
                    <p>Belum ada produk untuk event ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 30%">Nama Produk</th>
                                <th>Tipe</th>
                                <th>Harga Satuan</th>
                                <th>Stok</th>
                                <th>Event Terkait</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $product->nama_produk }}</div>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;">
                                            {{ $product->deskripsi }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($product->tipe == 'Fisik')
                                            <span class="badge bg-info text-dark"><i class="fas fa-box me-1"></i> Fisik</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="fas fa-ticket-alt me-1"></i> Digital</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">Rp {{ number_format($product->harga, 0, ',', '.') }}</td>
                                    <td>
                                        @if($product->stok <= 5)
                                            <span class="text-danger fw-bold">{{ $product->stok }} (Low)</span>
                                        @else
                                            <span class="text-success fw-bold">{{ $product->stok }}</span>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($product->event)
                                            <a href="{{ route('public.event.detail', $product->event_id) }}" target="_blank" class="text-decoration-none">
                                                <i class="fas fa-link me-1"></i> {{ Str::limit($product->event->judul, 20) }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.products.edit', $product->product_id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $product->product_id) }}" method="POST" class="d-inline" id="delete-product-{{ $product->product_id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmAction(event, 'Hapus Produk?', 'Yakin ingin menghapus produk &quot;{{ $product->nama_produk }}&quot;?', 'delete-product-{{ $product->product_id }}')" 
                                                        title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection