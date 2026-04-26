@extends('layouts.admin')

@section('title', 'Manajemen Bookings / Tiket')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center bg-white gap-2">
        <h6 class="m-0 fw-bold text-primary">Manajemen Bookings</h6>
        <div class="d-flex flex-wrap gap-2">
             <div class="btn-group">
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-secondary {{ request('status') != 'scanned' && request('status') != 'unscanned' ? 'active' : '' }}">Semua</a>
                <a href="{{ route('admin.bookings.index', ['status' => 'scanned', 'event_id' => request('event_id')]) }}" class="btn btn-sm btn-outline-success {{ request('status') == 'scanned' ? 'active' : '' }}">Sudah Scan</a>
                <a href="{{ route('admin.bookings.index', ['status' => 'unscanned', 'event_id' => request('event_id')]) }}" class="btn btn-sm btn-outline-warning {{ request('status') == 'unscanned' ? 'active' : '' }}">Belum Scan</a>
             </div>
             
             <button onclick="confirmReset()" class="btn btn-sm btn-danger">
                <i class="fas fa-trash-alt me-1"></i> Reset Data
             </button>
             <form id="reset-form" action="{{ route('admin.bookings.empty') }}" method="POST" style="display: none;">
                 @csrf
                 @method('DELETE')
             </form>
        </div>
    </div>

    <div class="card-body">
        <script>
            function confirmReset() {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Semua data booking (tiket & order) akan DIHAPUS PERMANEN! Tindakan ini tidak dapat dibatalkan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus Semua!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('reset-form').submit();
                    }
                })
            }
        </script>

        {{-- FILTER EVENT --}}
        <div class="bg-light rounded-3 p-3 mb-4">
            <form action="{{ route('admin.bookings.index') }}" method="GET" class="row g-3 align-items-center">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="col-auto">
                    <label for="event_id" class="col-form-label fw-bold"><i class="fas fa-filter me-1"></i> Pilih Event:</label>
                </div>
                <div class="col-md-5">
                    <select name="event_id" id="event_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Event Untuk Menampilkan Data --</option>
                        @foreach($availableEvents as $id => $judul)
                            <option value="{{ $id }}" {{ $selectedEventId == $id ? 'selected' : '' }}>{{ $judul }}</option>
                        @endforeach
                    </select>
                </div>
                
                @if($selectedEventId)
                <div class="col-auto">
                    <a href="{{ route('admin.events.export_participants', $selectedEventId) }}" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                </div>
                @endif
            </form>
        </div>

        {{-- Search Form --}}
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="mb-4">
            @if(request('event_id'))
                <input type="hidden" name="event_id" value="{{ request('event_id') }}">
            @endif
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Cari Ticket Code / Order ID / Nama Customer..." value="{{ request('q') }}">
                <button class="btn btn-dark" type="submit"><i class="fas fa-search"></i> Cari</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ticket Code</th>
                        <th>Scan Status</th>
                        <th>Event / Produk</th>
                        <th>Customer</th>
                        <th>Order Ref</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $ticket)
                    <tr>
                        <td>
                            <span class="fw-bold font-monospace text-primary">{{ $ticket->ticket_code }}</span>
                        </td>
                        <td>
                            @if($ticket->is_scanned)
                                <span class="badge bg-success rounded-pill px-3">
                                    <i class="fas fa-check-circle me-1"></i> SCANNED
                                </span>
                                <div class="small text-muted mt-1">
                                    {{ $ticket->scanned_at ? $ticket->scanned_at->format('d M Y H:i') : '-' }}
                                    <br>by {{ $ticket->scannedBy ? $ticket->scannedBy->name : 'System' }}
                                </div>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-3">
                                    <i class="fas fa-clock me-1"></i> BELUM SCAN
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $ticket->orderItem->product->nama_produk ?? '-' }}</div>
                            @if($ticket->orderItem->product && $ticket->orderItem->product->event)
                            <div class="small text-muted"><i class="fas fa-calendar-alt me-1"></i> {{ $ticket->orderItem->product->event->judul }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $ticket->orderItem->order->customer->name ?? 'Guest' }}</div>
                            <div class="small text-muted">{{ $ticket->orderItem->order->customer->email ?? '-' }}</div>
                            <div class="small text-muted">{{ $ticket->orderItem->order->customer->phone ?? '-' }}</div>
                        </td>
                        <td>
                            <a href="#" class="text-decoration-none font-monospace">#{{ $ticket->orderItem->order->order_id }}</a>
                            <div class="small text-success fw-bold">{{ $ticket->orderItem->order->status }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            @if($selectedEventId)
                                Tidak ada tiket ditemukan untuk event ini.
                            @else
                                Pilih event terlebih dahulu untuk menampilkan data tiket.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $bookings->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
