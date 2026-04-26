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
    const kategoriSelect = document.getElementById('kategori_tiket');
    const stokInput = document.getElementById('stok');
    const stokHelper = document.getElementById('stok-helper');

    function toggleStokInput() {
        if (kategoriSelect.value === 'seating') {
            stokInput.readOnly = true;
            stokHelper.style.display = 'block';
        } else {
            stokInput.readOnly = false;
            stokHelper.style.display = 'none';
        }
    }

    if (kategoriSelect) {
        kategoriSelect.addEventListener('change', toggleStokInput);
        toggleStokInput();
    }
});
</script>
@endpush

