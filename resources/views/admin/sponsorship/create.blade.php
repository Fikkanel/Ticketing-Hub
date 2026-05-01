@extends('layouts.admin')

@section('title', 'Generate Sponsorship Tickets')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Generate Sponsorship Tickets</h1>
        <a href="{{ route('admin.sponsorships.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Form Generate</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sponsorships.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">Nama Sponsorship (Misal: BRI, Mandiri, dsb)</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="event_id">Pilih Event</label>
                            <select class="form-control @error('event_id') is-invalid @enderror" id="event_id" name="event_id" required>
                                <option value="">-- Pilih Event --</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->event_id }}" {{ old('event_id') == $event->event_id ? 'selected' : '' }}>
                                        {{ $event->judul }}
                                    </option>
                                @endforeach
                            </select>
                            @error('event_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="product_id">Pilih Kategori Tiket</label>
                            <select class="form-control @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required disabled>
                                <option value="">-- Pilih Kategori Tiket --</option>
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pilih event terlebih dahulu untuk memuat kategori tiket.</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="quantity">Jumlah Tiket yang Digenerate</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" min="1" max="10000" required>
                            @error('quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maksimal 10.000 tiket per generate.</small>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block py-2 fw-bold" onclick="return confirm('Apakah Anda yakin ingin men-generate tiket sebanyak ini? Proses ini tidak dapat dibatalkan.')">
                            <i class="fas fa-magic me-2"></i> Generate Tiket Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card bg-info text-white shadow">
                <div class="card-body">
                    <h5 class="font-weight-bold"><i class="fas fa-info-circle me-2"></i> Informasi Fitur</h5>
                    <p class="mb-0">
                        Fitur ini digunakan untuk membuat tiket sponsorship secara massal. 
                        Tiket yang digenerate akan memiliki link akses khusus melalui subdomain <strong>access.tixkita.id</strong>.
                    </p>
                    <ul class="mt-3">
                        <li>Tiket tidak dikirim via email.</li>
                        <li>Tiket tercatat sebagai transaksi di sistem.</li>
                        <li>Sponsor dapat mengakses tiket via link rahasia.</li>
                        <li>Setiap tiket memiliki QR Code unik yang scannable.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('event_id').addEventListener('change', function() {
        const eventId = this.value;
        const productSelect = document.getElementById('product_id');
        
        if (!eventId) {
            productSelect.disabled = true;
            productSelect.innerHTML = '<option value="">-- Pilih Kategori Tiket --</option>';
            return;
        }

        // Fetch products for the selected event
        fetch(`/admin/products?event_id=${eventId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            productSelect.disabled = false;
            productSelect.innerHTML = '<option value="">-- Pilih Kategori Tiket --</option>';
            data.forEach(product => {
                productSelect.innerHTML += `<option value="${product.product_id}">${product.nama_produk} (Stok: ${product.stok})</option>`;
            });
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            // Fallback: If API doesn't return JSON, try to handle or show error
        });
    });
</script>
@endsection
