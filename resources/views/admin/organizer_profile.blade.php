@extends('layouts.admin')

@section('title', 'Profil Organizer')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Form Card --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                    <h6 class="m-0 fw-bold text-primary">Profil Organizer</h6>
                    @if(auth()->user()->hasOrganizerProfile())
                        <a href="{{ route('public.organizer.profile', auth()->user()->organizer_slug) }}" 
                           target="_blank" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt me-1"></i> Lihat Profil
                        </a>
                    @endif
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.organizer.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Logo Preview --}}
                        <div class="text-center mb-4">
                            <img src="{{ auth()->user()->organizer_logo_src }}" 
                                 id="logoPreview"
                                 class="rounded-circle shadow mb-3" 
                                 width="120" height="120" 
                                 style="object-fit: cover; border: 4px solid #e9ecef;">
                            <div>
                                <label for="organizer_logo" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-camera me-1"></i> Ubah Logo
                                </label>
                                <input type="file" name="organizer_logo" id="organizer_logo" class="d-none" accept="image/*">
                            </div>
                            <small class="text-muted">Rekomendasi: 200x200px, format JPG/PNG</small>
                        </div>

                        <hr class="my-4">

                        {{-- Nama Organizer --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Organizer <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="organizer_name" 
                                       class="form-control @error('organizer_name') is-invalid @enderror" 
                                       value="{{ old('organizer_name', auth()->user()->organizer_name) }}"
                                       placeholder="Contoh: Bozz Event"
                                       required>
                                <small class="text-muted">Nama yang akan ditampilkan di halaman publik</small>
                                @error('organizer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Slug URL</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">{{ url('/organizer') }}/</span>
                                    <input type="text" 
                                           name="organizer_slug" 
                                           class="form-control @error('organizer_slug') is-invalid @enderror" 
                                           value="{{ old('organizer_slug', auth()->user()->organizer_slug) }}"
                                           placeholder="bozz-event">
                                </div>
                                <small class="text-muted">Kosongkan untuk auto-generate dari nama</small>
                                @error('organizer_slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="organizer_description" 
                                      class="form-control @error('organizer_description') is-invalid @enderror" 
                                      rows="3"
                                      placeholder="Ceritakan tentang organizer Anda...">{{ old('organizer_description', auth()->user()->organizer_description) }}</textarea>
                            @error('organizer_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-address-card me-2"></i>Informasi Kontak</h6>

                        {{-- Kontak --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email Publik</label>
                                <input type="email" 
                                       name="organizer_email" 
                                       class="form-control @error('organizer_email') is-invalid @enderror" 
                                       value="{{ old('organizer_email', auth()->user()->organizer_email) }}"
                                       placeholder="info@organizer.com">
                                @error('organizer_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Telepon</label>
                                <input type="text" 
                                       name="organizer_phone" 
                                       class="form-control @error('organizer_phone') is-invalid @enderror" 
                                       value="{{ old('organizer_phone', auth()->user()->organizer_phone) }}"
                                       placeholder="08123456789">
                                @error('organizer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kota</label>
                                <input type="text" 
                                       name="organizer_city" 
                                       class="form-control @error('organizer_city') is-invalid @enderror" 
                                       value="{{ old('organizer_city', auth()->user()->organizer_city) }}"
                                       placeholder="Jakarta">
                                @error('organizer_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fab fa-instagram"></i></span>
                                    <input type="url" 
                                           name="organizer_instagram" 
                                           class="form-control @error('organizer_instagram') is-invalid @enderror" 
                                           value="{{ old('organizer_instagram', auth()->user()->organizer_instagram) }}"
                                           placeholder="https://instagram.com/organizer">
                                </div>
                                @error('organizer_instagram')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Simpan Profil
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('organizer_logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('logoPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
