@extends('layouts.admin')

@section('title', 'Manajemen Kode Diskon')

@section('content')

    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 fw-bold text-primary">Kode Promo & Voucher</h6>
        </div>
    </div>

    <div class="row">
        {{-- KARTU 1: Generate Otomatis --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3 bg-white border-bottom-primary" style="border-bottom: 3px solid var(--admin-primary);">
                    <h6 class="m-0 fw-bold text-primary"><i class="fas fa-magic me-2"></i>Generate Kode Otomatis</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.discounts.generate') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">EVENT</label>
                            <select name="event_id" class="form-select" {{ !auth()->user()->isSuperAdmin() ? 'required' : '' }}>
                                @if(auth()->user()->isSuperAdmin())
                                    <option value="">-- Berlaku Global (Semua Event) --</option>
                                @else
                                    <option value="" disabled selected>-- Pilih Event --</option>
                                @endif
                                @foreach($events as $event)
                                    <option value="{{ $event->event_id }}">{{ $event->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">PERSENTASE (%)</label>
                            <div class="input-group">
                                <input type="number" name="generate_percentage" class="form-control" placeholder="Contoh: 20" min="1" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">KUOTA PENGGUNAAN</label>
                            <input type="number" name="generate_max_uses" class="form-control" placeholder="Contoh: 100" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm mt-3">
                            <i class="fas fa-cogs me-2"></i> Generate Kode
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KARTU 2: Buat Manual --}}
        <div class="col-lg-7 mb-4">
            <div class="card shadow mb-4 h-100">
                <div class="card-header py-3 bg-white border-bottom-success" style="border-bottom: 3px solid #1cc88a;">
                    <h6 class="m-0 fw-bold text-success"><i class="fas fa-edit me-2"></i>Buat Kode Manual</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.discounts.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-muted">KODE UNIK</label>
                                <input type="text" name="code" class="form-control text-uppercase font-monospace" placeholder="MISAL: LEBARAN50" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-muted">PERSENTASE (%)</label>
                                <div class="input-group">
                                    <input type="number" name="percentage" class="form-control" placeholder="1-100" min="1" max="100" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">EVENT</label>
                            <select name="event_id" class="form-select" {{ !auth()->user()->isSuperAdmin() ? 'required' : '' }}>
                                @if(auth()->user()->isSuperAdmin())
                                    <option value="">-- Berlaku Global (Semua Event) --</option>
                                @else
                                    <option value="" disabled selected>-- Pilih Event --</option>
                                @endif
                                @foreach($events as $event)
                                    <option value="{{ $event->event_id }}">{{ $event->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-muted">KUOTA</label>
                                <input type="number" name="max_uses" class="form-control" value="1" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small fw-bold text-muted">EXPIRED (OPSIONAL)</label>
                                <input type="datetime-local" name="expires_at" class="form-control">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold shadow-sm mt-2">
                            <i class="fas fa-save me-2"></i> Simpan Kode
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL DATA --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 fw-bold text-gray-800">Daftar Kode Aktif</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Promo</th>
                            <th>Event</th>
                            <th>Besar Diskon</th>
                            <th>Penggunaan</th>
                            <th>Status</th>
                            <th>Kadaluarsa</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($discounts as $discount)
                            <tr>
                                <td class="font-monospace fw-bold text-primary">{{ $discount->code }}</td>
                                <td>
                                    @if($discount->event)
                                        <span class="badge bg-light text-dark border">{{ Str::limit($discount->event->judul, 20) }}</span>
                                    @else
                                        <span class="badge bg-dark">GLOBAL</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info text-dark">{{ $discount->percentage }}% OFF</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2 text-xs fw-bold">{{ $discount->used_count }}/{{ $discount->max_uses }}</span>
                                        <div class="progress progress-sm w-100">
                                            @php $percent = ($discount->used_count / $discount->max_uses) * 100; @endphp
                                            <div class="progress-bar bg-{{ $percent >= 100 ? 'danger' : 'primary' }}" role="progressbar" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if (!$discount->is_active)
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @elseif ($discount->used_count >= $discount->max_uses)
                                        <span class="badge bg-danger">Habis</span>
                                    @elseif ($discount->expires_at && \Carbon\Carbon::parse($discount->expires_at)->isPast())
                                        <span class="badge bg-warning text-dark">Expired</span>
                                    @else
                                        <span class="badge bg-success">Aktif</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $discount->expires_at ? \Carbon\Carbon::parse($discount->expires_at)->format('d M Y, H:i') : 'Selamanya' }}
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.discounts.destroy', $discount->discount_id) }}" method="POST" id="delete-discount-{{ $discount->discount_id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm btn-circle shadow-sm" 
                                                onclick="confirmAction(event, 'Hapus Kode?', 'Yakin ingin menghapus kode &quot;{{ $discount->code }}&quot;?', 'delete-discount-{{ $discount->discount_id }}')" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kode diskon.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection