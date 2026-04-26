@extends('layouts.admin')

@section('title', 'Edit Banner')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Banner</h1>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Desktop (1300x300)</label>
                        <div class="mb-2">
                            <img src="{{ $banner->banner_src }}" alt="Desktop Preview" class="img-fluid rounded border" style="max-height: 150px; width: 100%; object-fit: cover;">
                        </div>
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Upload baru untuk mengganti.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mobile (1300x500)</label>
                        <div class="mb-2">
                            @if($banner->mobile_image_path)
                                <img src="{{ $banner->mobile_banner_src }}" alt="Mobile Preview" class="img-fluid rounded border" style="max-height: 250px; width: 100%; object-fit: cover;">
                            @else
                                <div class="p-4 bg-light text-center border rounded text-muted">Belum ada gambar mobile</div>
                            @endif
                        </div>
                        <input type="file" name="image_mobile" id="image_mobile" class="form-control @error('image_mobile') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Upload baru untuk mengganti.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Judul (Opsional)</label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $banner->title) }}">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="url" class="form-label">Link Tujuan (Opsional)</label>
                    <input type="url" name="url" id="url" class="form-control @error('url') is-invalid @enderror" value="{{ old('url', $banner->url) }}">
                    @error('url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="order" class="form-label">Urutan Tampilan</label>
                        <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $banner->order) }}">
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-center">
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $banner->is_active ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_active">
                                Aktifkan Banner?
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
