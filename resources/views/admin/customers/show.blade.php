@extends('layouts.admin')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
            <h1 class="h3 text-gray-800 fw-bold mb-0">Detail Pelanggan</h1>
        </div>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-light border shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    <div class="row g-4">
        {{-- PROFILE CARD --}}
        <div class="col-xl-4 col-md-5">
            <div class="card shadow mb-4 h-100 border-0">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x text-primary"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">{{ $customer->name }}</h4>
                    <p class="text-muted small mb-4">Bergabung {{ $customer->created_at->format('d M Y') }}</p>
                    
                    <div class="text-start bg-light rounded-3 p-4">
                        <div class="mb-3">
                            <label class="small text-uppercase fw-bold text-muted mb-1">Email</label>
                            <div class="d-flex align-items-center text-dark">
                                <i class="fas fa-envelope me-2 text-primary"></i>
                                {{ $customer->email }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small text-uppercase fw-bold text-muted mb-1">No. HP</label>
                            <div class="d-flex align-items-center text-dark">
                                <i class="fas fa-phone me-2 text-primary"></i>
                                {{ $customer->phone ?? '-' }}
                            </div>
                        </div>
                        <div>
                            <label class="small text-uppercase fw-bold text-muted mb-1">Alamat</label>
                            <div class="d-flex align-items-start text-dark">
                                <i class="fas fa-map-marker-alt me-2 mt-1 text-primary"></i>
                                <span>{{ $customer->address ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS & ORDERS --}}
        <div class="col-xl-8 col-md-7">
            {{-- STATS ROW --}}
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm h-100 bg-primary text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-index-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small text-uppercase fw-bold mb-1">Total Transaksi</div>
                                    <div class="h2 mb-0 fw-bold">{{ $customer->orders->count() }}</div>
                                </div>
                                <i class="fas fa-shopping-bag fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card border-0 shadow-sm h-100 bg-success text-white overflow-hidden position-relative">
                        <div class="card-body p-4 position-relative z-index-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small text-uppercase fw-bold mb-1">Total Pengeluaran</div>
                                    <div class="h2 mb-0 fw-bold">Rp {{ number_format($customer->orders->where('status', 'Paid')->sum('total_harga'), 0, ',', '.') }}</div>
                                </div>
                                <i class="fas fa-wallet fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ORDER HISTORY --}}
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-dark">Riwayat Pesanan Terakhir</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small text-secondary">
                                <tr>
                                    <th class="ps-4 py-3">Invoice</th>
                                    <th>Event</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->orders as $order)
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary small">#{{ $order->order_id }}</td>
                                        <td>
                                            @php
                                                // Ambil nama event dari item pertama
                                                $eventName = $order->orderItems->first()?->product?->event?->judul ?? 'Event Dihapus';
                                            @endphp
                                            <div class="text-dark fw-bold small text-truncate" style="max-width: 200px;">
                                                {{ $eventName }}
                                            </div>
                                        </td>
                                        <td class="small text-muted">
                                            {{ $order->created_at->format('d M Y') }}
                                        </td>
                                        <td class="small fw-bold">
                                            Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusColor = match($order->status) {
                                                    'Paid' => 'success',
                                                    'Pending' => 'warning',
                                                    'Shipped' => 'info',
                                                    'Cancelled' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-soft-{{ $statusColor }} text-{{ $statusColor }} rounded-pill px-2 py-1 small">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route('public.invoice', $order->order_id) }}" target="_blank" class="btn btn-sm btn-light border text-primary" title="Lihat Invoice">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small">Belum ada riwayat pesanan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
