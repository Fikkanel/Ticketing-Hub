@extends('layouts.admin')

@section('title', 'Tambah Banner')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Tambah Banner Baru</h1>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="image" class="form-label fw-bold">Gambar Banner Desktop <span class="text-danger">*</span></label>
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*" required>
                        <div class="form-text">Ukuran: <strong>1300 x 300 px</strong>.</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="image_mobile" class="form-label fw-bold">Gambar Banner Mobile</label>
                        <input type="file" name="image_mobile" id="image_mobile" class="form-control @error('image_mobile') is-invalid @enderror" accept="image/*">
                        <div class="form-text">Ukuran: <strong>1300 x 500 px</strong>. Jika kosong, pakai gambar desktop.</div>
                        @error('image_mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Judul (Opsional)</label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Contoh: Promo Akhir Tahun">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="url" class="form-label">Link Tujuan (Opsional)</label>
                    <input type="url" name="url" id="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url') }}" placeholder="https://...">
                    <div class="form-text">Jika diisi, banner akan dapat diklik menuju link ini.</div>
                    @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="order" class="form-label">Urutan Tampilan</label>
                        <input type="number" name="order" id="order" class="form-control" value="0">
                        <div class="form-text">Angka lebih kecil tampil lebih dulu.</div>
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-center">
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label fw-bold" for="is_active">
                                Aktifkan Banner?
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
