@extends('layouts.admin')

@section('title', 'Edit Produk: ' . $product->nama_produk)

@section('content')
    <form action="{{ route('admin.products.update', $product->product_id) }}" method="POST">
        @csrf
        @method('PUT') {{-- Penting! Menggunakan metode PUT untuk Update --}}

        <div class="row">
            <div class="col-md-6">
                {{-- 1. Nama Produk --}}
                <div class="mb-3">
                    <label for="nama_produk" class="form-label">Nama Produk</label>
                    <input type="text" class="form-control @error('nama_produk') is-invalid @enderror" id="nama_produk" name="nama_produk" value="{{ old('nama_produk', $product->nama_produk) }}" required>
                    @error('nama_produk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- 2. Harga --}}
                <div class="mb-3">
                    <label for="harga" class="form-label">Harga (Rp)</label>
                    <input type="number" step="0.01" class="form-control @error('harga') is-invalid @enderror" id="harga" name="harga" value="{{ old('harga', $product->harga) }}" required>
                    @error('harga')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                
                {{-- 3. Stok --}}
                <div class="mb-3">
                    <label for="stok" class="form-label">Stok</label>
                    <input type="number" class="form-control @error('stok') is-invalid @enderror" id="stok" name="stok" value="{{ old('stok', $product->stok) }}" required>
                    <div id="stok-helper" class="form-text text-info mt-1" style="display:none;">
                        <i class="fas fa-info-circle"></i> Stok tiket Seating akan menyesuaikan otomatis dengan jumlah kursi aktif di menu Layout.
                    </div>
                    @error('stok')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-md-6">
                {{-- 4. Tipe Produk (Fisik / Digital) --}}
                <div class="mb-3">
                    <label for="tipe" class="form-label">Tipe Produk</label>
                    <select class="form-select @error('tipe') is-invalid @enderror" id="tipe" name="tipe" required>
                        <option value="">Pilih Tipe</option>
                        {{-- Menggunakan $product->tipe untuk menentukan opsi yang dipilih --}}
                        <option value="Fisik" {{ old('tipe', $product->tipe) == 'Fisik' ? 'selected' : '' }}>Fisik (Travel Kit)</option>
                        <option value="Digital" {{ old('tipe', $product->tipe) == 'Digital' ? 'selected' : '' }}>Digital (Voucher/Aksesoris)</option>
                        <option value="Seminar" {{ old('tipe', $product->tipe) == 'Seminar' ? 'selected' : '' }}>Digital (Seminar)</option>
                    </select>
                    @error('tipe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Kategori Tiket (Standing / Seating) --}}
                <div class="mb-3">
                    <label for="kategori_tiket" class="form-label">Kategori Tiket</label>
                    <select class="form-select @error('kategori_tiket') is-invalid @enderror" id="kategori_tiket" name="kategori_tiket">
                        <option value="">Tidak Ada Kategori (Bukan Tiket)</option>
                        <option value="standing" {{ old('kategori_tiket', $product->kategori_tiket) == 'standing' ? 'selected' : '' }}>Standing (Berdiri)</option>
                        <option value="seating" {{ old('kategori_tiket', $product->kategori_tiket) == 'seating' ? 'selected' : '' }}>Seating (Kursi/Denah)</option>
                    </select>
                    @error('kategori_tiket')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Tanggal Berlaku Tiket --}}
                <div class="mb-3">
                    <label for="valid_date" class="form-label">Tanggal Berlaku <span class="badge bg-secondary">Opsional</span></label>
                    <input type="date" class="form-control @error('valid_date') is-invalid @enderror" id="valid_date" name="valid_date" value="{{ old('valid_date', optional($product->valid_date)->format('Y-m-d')) }}">
                    <div class="form-text text-muted">
                        <i class="fas fa-calendar-alt"></i> Jika diisi, tiket ini HANYA BISA di-scan pada tanggal yang ditentukan. Kosongkan jika tiket berlaku kapan saja.
                    </div>
                    @error('valid_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- 5. Keterikatan Event --}}
                <div class="mb-3">
                    <label for="event_id" class="form-label">Terikat dengan Event (Opsional)</label>
                    <select class="form-select @error('event_id') is-invalid @enderror" id="event_id" name="event_id">
                        {{-- $events di-pluck di ProductController --}}
                        @foreach ($events as $id => $judul)
                            {{-- Menggunakan $product->event_id untuk menentukan opsi yang dipilih --}}
                            <option value="{{ $id }}" {{ old('event_id', $product->event_id) == $id ? 'selected' : '' }}>{{ $judul }}</option>
                        @endforeach
                    </select>
                    @error('event_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- 6. Tiket Sponsorship --}}
                <div class="mb-3 form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" id="is_sponsorship" name="is_sponsorship" value="1" {{ old('is_sponsorship', $product->is_sponsorship) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="is_sponsorship">
                        Jadikan Tiket Sponsorship
                        <small class="d-block text-muted fw-normal mt-1"><i class="fas fa-info-circle text-info"></i> Jika dicentang, tiket/kategori ini <b>tidak akan ditampilkan</b> pada form pembelian publik. Hanya bisa diakses via menu Sponsorship di Admin.</small>
                    </label>
                </div>

                {{-- 7. Khusus Bundle (Hidden) --}}
                <div class="mb-3 form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" id="is_hidden" name="is_hidden" value="1" {{ old('is_hidden', $product->is_hidden) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="is_hidden">
                        Sembunyikan dari Publik (Khusus Bundle)
                        <small class="d-block text-muted fw-normal mt-1"><i class="fas fa-eye-slash text-warning"></i> Jika dicentang, tiket ini <b>tidak bisa dibeli satuan</b> oleh pembeli. Hanya bisa dijual sebagai isi dari Paket Bundle.</small>
                    </label>
                </div>

            </div>
        </div>

        {{-- 7. Deskripsi --}}
        <div class="mb-3">
            <label for="deskripsi" class="form-label">Deskripsi Produk</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4">{{ old('deskripsi', $product->deskripsi) }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        @if($product->kategori_tiket === 'seating')
            <a href="{{ route('admin.products.seat_layout', $product->product_id) }}" class="btn btn-primary-custom ms-2">Atur Layout Kursi</a>
        @endif
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary ms-2">Batal</a>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isHiddenCheckbox = document.getElementById('is_hidden');
    const stokInput = document.getElementById('stok');
    const kategoriSelect = document.getElementById('kategori_tiket');
    const stokHelper = document.getElementById('stok-helper');

    function updateStokBehavior() {
        const isBundleOnly = isHiddenCheckbox && isHiddenCheckbox.checked;
        const isSeating = kategoriSelect && kategoriSelect.value === 'seating';

        if (isBundleOnly) {
            // Bundle Only: Stock is irrelevant
            stokInput.readOnly = true;
            stokInput.classList.add('bg-light');
            // We don't automatically override value in Edit if it's already set, 
            // unless it was 0. But for consistency with Create:
            if (stokInput.value == 0) stokInput.value = 999999;
            
            if (stokHelper) {
                stokHelper.innerHTML = '<i class="fas fa-info-circle"></i> Stok dinonaktifkan karena produk ini khusus Bundle (Stok diatur di menu Bundle).';
                stokHelper.style.display = 'block';
                stokHelper.classList.remove('text-info');
                stokHelper.classList.add('text-warning');
            }
        } else if (isSeating) {
            // Seating: Stock managed by layout
            stokInput.readOnly = true;
            stokInput.classList.add('bg-light');
            if (stokHelper) {
                stokHelper.innerHTML = '<i class="fas fa-info-circle"></i> Stok tiket Seating akan menyesuaikan otomatis dengan jumlah kursi aktif di menu Layout.';
                stokHelper.style.display = 'block';
                stokHelper.classList.remove('text-warning');
                stokHelper.classList.add('text-info');
            }
        } else {
            // Regular Product: Manual stock
            stokInput.readOnly = false;
            stokInput.classList.remove('bg-light');
            if (stokHelper) stokHelper.style.display = 'none';
        }
    }

    if (isHiddenCheckbox) isHiddenCheckbox.addEventListener('change', updateStokBehavior);
    if (kategoriSelect) kategoriSelect.addEventListener('change', updateStokBehavior);
    
    updateStokBehavior();
});
</script>
@endpush

