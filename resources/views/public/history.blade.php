@extends('layouts.public')

@section('title', 'Riwayat Pesanan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold"><i class="fas fa-history me-2"></i> Riwayat Pesanan</h2>
            <a href="{{ route('public.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda
            </a>
        </div>

        @if($orders->isEmpty())
            <div class="text-center py-5">
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-cart-2130356-1800917.png" alt="Empty" style="width: 200px; opacity: 0.7;">
                <h4 class="mt-4 fw-bold">Belum ada pesanan</h4>
                <p class="text-muted">Anda belum melakukan pemesanan apapun di perangkat ini.</p>
                <a href="{{ route('public.index') }}" class="btn btn-primary-custom px-4 mt-2">Jelajahi Event</a>
            </div>
        @else
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 ps-4">ID Pesanan</th>
                                    <th class="py-3">Event / Produk</th>
                                    <th class="py-3">Tanggal</th>
                                    <th class="py-3">Total</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 pe-4 text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td class="ps-4 fw-bold">#{{ $order->order_id }}</td>
                                        <td>
                                            @foreach($order->orderItems as $item)
                                                <div class="small fw-semibold">{{ $item->product->nama_produk ?? 'Produk Dihapus' }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    {{ $item->product->event->judul ?? '-' }} (x{{ $item->kuantitas }})
                                                </div>
                                            @endforeach
                                        </td>
                                        <td class="small">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y H:i') }}</td>
                                        <td class="fw-bold text-primary-custom">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                                        <td>
                                            @if($order->status == 'Paid')
                                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Lunas</span>
                                            @elseif($order->status == 'Pending')
                                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Menunggu Bayar</span>
                                            @elseif($order->status == 'Cancelled')
                                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Batal</span>
                                            @else
                                                <span class="badge bg-secondary px-3 py-2 rounded-pill">{{ $order->status }}</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 text-end">
                                            @if($order->status == 'Pending')
                                                <a href="{{ route('public.invoice', $order->order_id) }}" class="btn btn-primary-custom btn-sm rounded-pill px-3">
                                                    Bayar <i class="fas fa-chevron-right ms-1"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('public.invoice', $order->order_id) }}" class="btn btn-outline-primary-custom btn-sm rounded-pill px-3">
                                                    Detail
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-4 d-flex align-items-center" role="alert">
                <i class="fas fa-info-circle fa-lg me-3"></i>
                <div>
                    <strong>Info Session:</strong> Riwayat ini tersimpan di browser Anda. Jika Anda menghapus cache atau pindah perangkat, riwayat ini mungkin tidak muncul.
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
