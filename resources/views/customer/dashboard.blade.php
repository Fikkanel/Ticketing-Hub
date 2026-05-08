@extends('layouts.public')

@section('title', 'Dashboard - Ticketing Hub')

@section('content')
<style>
    .dash-tabs .nav-link {
        color: #555 !important;
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.75rem 1rem;
        border-radius: 0;
        transition: all 0.2s ease;
    }
    .dash-tabs .nav-link.active {
        color: var(--primary-color, #3a7d44) !important;
        border-bottom-color: var(--primary-color, #3a7d44);
        background: transparent;
    }
    .dash-tabs .nav-link:hover {
        color: var(--primary-color, #3a7d44) !important;
    }
    .dash-profile-header {
        background: var(--primary-color, #3a7d44);
        border-radius: 16px;
        padding: 1.25rem;
        color: #fff;
    }
    .dash-profile-header .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .dash-stat-pill {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        text-align: center;
        flex: 1;
    }
    .order-card {
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: box-shadow 0.2s ease;
    }
    .order-card:hover {
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.9rem;
    }
    .info-row:last-child {
        border-bottom: none;
    }
</style>

<div class="container py-4">

    {{-- Compact Profile Header --}}
    <div class="dash-profile-header mb-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <div class="flex-grow-1 min-w-0">
                <h5 class="fw-bold mb-0 text-truncate">{{ $customer->name }}</h5>
                <small class="opacity-75">{{ $customer->email }}</small>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm rounded-pill d-none d-md-inline-block">
                <i class="fas fa-calendar-alt me-1"></i> Event
            </a>
        </div>
    </div>

    {{-- Quick Stats Row --}}
    <div class="d-flex gap-2 mb-3">
        <div class="dash-stat-pill">
            <div class="fw-bold fs-5 text-primary-custom">{{ $orders->total() }}</div>
            <div class="text-muted" style="font-size: 0.75rem;">Total Pesanan</div>
        </div>
        <div class="dash-stat-pill">
            @php
                $memberId = $customer->unix_id ?? 'TIX' . str_pad($customer->id, 6, '0', STR_PAD_LEFT);
            @endphp
            <div class="fw-bold fs-6 text-primary-custom font-monospace">{{ $memberId }}</div>
            <div class="text-muted" style="font-size: 0.75rem;">ID Member</div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav dash-tabs border-bottom mb-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pesanan" type="button" role="tab">
                <i class="fas fa-receipt me-1"></i> Pesanan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-profil" type="button" role="tab">
                <i class="fas fa-user me-1"></i> Profil
            </button>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content">

        {{-- Tab: Pesanan (Default Active) --}}
        <div class="tab-pane fade show active" id="tab-pesanan" role="tabpanel">
            @if($orders->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Belum ada pesanan</h6>
                    <p class="text-muted small mb-3">Mulai jelajahi event dan pesan tiket sekarang!</p>
                    <a href="{{ route('home') }}" class="btn btn-primary-custom rounded-pill px-4 btn-sm">
                        <i class="fas fa-search me-1"></i> Cari Event
                    </a>
                </div>
            @else
                {{-- Mobile: Card-based orders --}}
                @foreach($orders as $order)
                    @php
                        $firstItem = $order->orderItems->first();
                        $eventName = $firstItem?->product?->event?->judul ?? '-';
                        $badgeClass = match($order->status) {
                            'Paid' => 'bg-success',
                            'Pending' => 'bg-warning text-dark',
                            'Cancelled' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                    @endphp
                    <div class="order-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-bold text-primary-custom small">#{{ $order->order_id }}</span>
                                <span class="badge {{ $badgeClass }} rounded-pill ms-2" style="font-size: 0.7rem;">{{ $order->status }}</span>
                            </div>
                            <small class="text-muted">{{ $order->created_at->format('d M Y') }}</small>
                        </div>
                        <p class="mb-2 fw-semibold text-dark" style="font-size: 0.9rem;">{{ Str::limit($eventName, 40) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">
                                @if($order->total_harga == 0)
                                    <span class="text-success">GRATIS</span>
                                @else
                                    Rp {{ number_format($order->total_harga, 0, ',', '.') }}
                                @endif
                            </span>
                            <a href="{{ route('public.invoice', $order->order_id) }}" 
                                class="btn btn-sm btn-outline-primary-custom rounded-pill px-3" target="_blank">
                                <i class="fas fa-eye me-1"></i> Lihat
                            </a>
                        </div>
                    </div>
                @endforeach

                {{-- Pagination --}}
                @if($orders->hasPages())
                    <div class="d-flex justify-content-center py-3">
                        {{ $orders->links() }}
                    </div>
                @endif
            @endif
        </div>

        {{-- Tab: Profil --}}
        <div class="tab-pane fade" id="tab-profil" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="info-row">
                        <span class="text-muted">Nama</span>
                        <span class="fw-bold text-end">{{ $customer->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="text-muted">ID Member</span>
                        <div class="d-flex align-items-center gap-2">
                            <code class="bg-light px-2 py-1 rounded text-primary-custom fw-bold small">{{ $memberId }}</code>
                            <button type="button" class="btn btn-sm btn-outline-primary-custom rounded-circle p-0" 
                                    style="width: 28px; height: 28px; font-size: 0.7rem;"
                                    onclick="navigator.clipboard.writeText('{{ $memberId }}'); this.innerHTML='<i class=\'fas fa-check\'></i>'; setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i>', 1500);"
                                    title="Salin ID">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                    <div class="info-row">
                        <span class="text-muted">Email</span>
                        <span class="fw-bold text-end">{{ $customer->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="text-muted">Telepon</span>
                        <span class="fw-bold">{{ $customer->phone ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="text-muted">Member Sejak</span>
                        <span class="fw-bold">{{ $customer->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Logout --}}
            <div class="mt-3">
                <form action="{{ route('customer.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">
                        <i class="fas fa-sign-out-alt me-2"></i> Keluar
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
