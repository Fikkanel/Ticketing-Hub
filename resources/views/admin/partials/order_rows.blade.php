@forelse ($orders as $order)
    <tr style="border-bottom: 1px solid #f0f0f0;">
        {{-- 1. Invoice ID --}}
        <td class="ps-4">
            <div class="fw-bold text-primary font-monospace">#{{ $order->order_id }}</div>
        </td>
        
        {{-- 2. Info Pelanggan --}}
        <td>
            <div class="d-flex align-items-center">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; color: #aaa;">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    {{-- PERBAIKAN: Menggunakan null-safe operator (?->) untuk keamanan data --}}
                    <div class="fw-bold text-dark mb-0">{{ $order->customer?->name ?? 'Tamu' }}</div>
                    <div class="text-muted small">
                        <i class="fas fa-envelope me-1" style="font-size: 0.7rem;"></i> 
                        {{ Str::limit($order->customer?->email ?? '-', 20) }}
                    </div>
                </div>
            </div>
        </td>

        {{-- 3. Total Harga --}}
        <td>
            <div class="fw-bold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</div>
            <div class="small text-muted">{{ $order->metode_pembayaran }}</div>
        </td>
        
        {{-- 4. Status Badge --}}
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
            <span class="badge bg-soft-{{ $statusColor }} text-{{ $statusColor }} border border-{{ $statusColor }} rounded-pill px-3 py-2">
                {{ $order->status }}
            </span>
        </td>

        {{-- 5. Tanggal --}}
        <td class="small text-muted">
            <div>{{ \Carbon\Carbon::parse($order->tgl_order)->format('d M Y') }}</div>
            <div>{{ \Carbon\Carbon::parse($order->tgl_order)->format('H:i') }} WIB</div>
        </td>
        
        {{-- 6. Aksi & Modal Detail --}}
        <td class="text-center">
            <button type="button" class="btn btn-primary btn-sm rounded-circle shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#detailModal-{{ $order->order_id }}" title="Lihat Detail">
                <i class="fas fa-eye"></i>
            </button>

            <div class="btn-group dropup">
                <button type="button" class="btn btn-light btn-sm rounded-circle border dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport">
                    <i class="fas fa-ellipsis-v text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="z-index: 1050;">
                    <li><h6 class="dropdown-header small text-uppercase">Update Status</h6></li>
                    <li>
                        <form action="{{ route('admin.orders.update_status', $order->order_id) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="Paid">
                            <button class="dropdown-item text-success"><i class="fas fa-check-circle me-2"></i> Lunas (Paid)</button>
                        </form>
                    </li>
                    <li>
                        <form action="{{ route('admin.orders.update_status', $order->order_id) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="Shipped">
                            <button class="dropdown-item text-info"><i class="fas fa-shipping-fast me-2"></i> Dikirim (Shipped)</button>
                        </form>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('admin.orders.update_status', $order->order_id) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="Cancelled">
                            <button class="dropdown-item text-danger"><i class="fas fa-ban me-2"></i> Batalkan</button>
                        </form>
                    </li>
                    
                    {{-- Resend Ticket (Superadmin Only, Paid Orders) --}}
                    @if(Auth::user()->isSuperAdmin() && $order->status === 'Paid')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form id="resendForm-{{ $order->order_id }}" action="{{ route('admin.orders.resend_ticket', $order->order_id) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <button type="button" class="dropdown-item text-primary" onclick="confirmResendTicket('{{ $order->order_id }}', '{{ $order->customer?->email ?? 'customer' }}')">
                                <i class="fas fa-paper-plane me-2"></i> Resend Tiket
                            </button>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- MODAL DETAIL ORDER --}}
            <div class="modal fade text-start" id="detailModal-{{ $order->order_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-primary text-white border-bottom-0" style="background: linear-gradient(135deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);">
                            <div>
                                <h5 class="modal-title fw-bold mb-0">Rincian Pesanan</h5>
                                <small class="opacity-75">Invoice: #{{ $order->order_id }}</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body bg-light">
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-gray-800 border-bottom pb-2 mb-3"><i class="fas fa-user-circle me-2 text-primary"></i>Data Pelanggan</h6>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="fw-bold text-dark">{{ $order->customer?->name ?? 'Tamu' }}</span>
                                                <span class="text-muted small"><i class="fas fa-envelope me-2 w-20"></i> {{ $order->customer?->email ?? '-' }}</span>
                                                <span class="text-muted small"><i class="fas fa-phone me-2 w-20"></i> {{ $order->customer?->phone ?? '-' }}</span>
                                                
                                                @if($order->buyer_nik)
                                                <span class="text-muted small"><i class="fas fa-id-card me-2 w-20"></i> NIK: <strong class="text-dark">{{ $order->buyer_nik }}</strong></span>
                                                @endif
                                                
                                                @if($order->buyer_dob)
                                                <span class="text-muted small"><i class="fas fa-calendar me-2 w-20"></i> Tgl Lahir: <strong class="text-dark">{{ \Carbon\Carbon::parse($order->buyer_dob)->format('d M Y') }}</strong></span>
                                                @endif
                                                
                                                @if($order->buyer_gender)
                                                <span class="text-muted small"><i class="fas fa-venus-mars me-2 w-20"></i> Gender: <strong class="text-dark">{{ $order->buyer_gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</strong></span>
                                                @endif
                                                
                                                @if($order->buyer_custom_data && is_array($order->buyer_custom_data))
                                                    @foreach($order->buyer_custom_data as $key => $value)
                                                    <span class="text-muted small"><i class="fas fa-tag me-2 w-20"></i> {{ ucwords(str_replace(['custom_field_', '_'], ['', ' '], $key)) }}: <strong class="text-dark">{{ $value }}</strong></span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <h6 class="fw-bold text-gray-800 border-bottom pb-2 mb-3"><i class="fas fa-wallet me-2 text-success"></i>Info Pembayaran</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Metode:</span>
                                                <span class="fw-bold text-dark">{{ $order->metode_pembayaran }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-muted small">Waktu Order:</span>
                                                <span class="text-dark small">{{ \Carbon\Carbon::parse($order->tgl_order)->format('d M Y, H:i') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">Status:</span>
                                                <span class="badge bg-{{ $statusColor }} px-3">{{ $order->status }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php
                                $digitalItems = $order->orderItems->filter(fn($i) => $i->product?->tipe == 'Digital');
                                $physicalItems = $order->orderItems->filter(fn($i) => $i->product?->tipe == 'Fisik');
                            @endphp

                            {{-- Item Digital --}}
                            @if($digitalItems->isNotEmpty())
                                <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                                    <div class="card-header bg-white border-bottom fw-bold text-primary">
                                        <i class="fas fa-qrcode me-2"></i>Tiket & Voucher
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 align-middle">
                                            <thead class="table-light small">
                                                <tr>
                                                    <th class="ps-3">Produk</th>
                                                    <th class="text-end pe-3">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($digitalItems as $item)
                                                    <tr>
                                                        <td class="ps-3 py-3">
                                                            <div class="fw-bold">{{ $item->product?->nama_produk ?? 'Produk Tidak Ditemukan' }}</div>
                                                            <small class="text-muted">Qty: {{ $item->kuantitas }}</small>
                                                        </td>
                                                        <td class="text-end pe-3 fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- Item Fisik --}}
                            @if($physicalItems->isNotEmpty())
                                <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                                    <div class="card-header bg-white border-bottom fw-bold text-info">
                                        <i class="fas fa-box-open me-2"></i>Barang Fisik
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        @foreach($physicalItems as $item)
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $item->product?->nama_produk ?? 'Produk Tidak Ditemukan' }}</div>
                                                    <div class="small text-muted">Jumlah: {{ $item->kuantitas }}</div>
                                                </div>
                                                <div class="fw-bold text-dark">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="card border-0 shadow-sm bg-white mt-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-end align-items-center gap-4">
                                        @if($order->diskon_amount > 0)
                                            <div class="text-end">
                                                <div class="small text-muted">Diskon ({{ $order->diskon_code }})</div>
                                                <div class="text-danger fw-bold">- Rp {{ number_format($order->diskon_amount, 0, ',', '.') }}</div>
                                            </div>
                                        @endif
                                        <div class="text-end">
                                            <div class="small text-muted text-uppercase fw-bold">Grand Total</div>
                                            <div class="fs-4 fw-bold text-primary">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 bg-white">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button>
                            <a href="{{ route('public.invoice', $order->order_id) }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-print me-2"></i> Cetak Invoice
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            {{-- END MODAL --}}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-5">
            <div class="d-flex flex-column align-items-center justify-content-center text-muted opacity-50">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <p class="mb-0">Tidak ada data pesanan yang ditemukan.</p>
            </div>
        </td>
    </tr>
@endforelse
