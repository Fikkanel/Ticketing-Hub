@extends('layouts.admin')

@section('title', isset($category) ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Kategori</a></li>
                <li class="breadcrumb-item active">{{ isset($category) ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">
            <i class="fas fa-{{ isset($category) ? 'edit' : 'plus' }} me-2"></i>
            {{ isset($category) ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
        </h4>
    </div>

    {{-- Form Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}" 
                  method="POST">
                @csrf
                @if(isset($category))
                    @method('PUT')
                @endif

                {{-- Nama Kategori --}}
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $category->name ?? '') }}"
                           placeholder="Contoh: Music, Sport, Seminar"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Slug akan digenerate otomatis dari nama kategori.</div>
                </div>

                {{-- Icon --}}
                <div class="mb-4">
                    <label for="icon" class="form-label fw-semibold">Icon (FontAwesome)</label>
                    <select class="form-select @error('icon') is-invalid @enderror" 
                            id="icon" 
                            name="icon">
                        <option value="">-- Pilih Icon --</option>
                        @foreach($icons as $iconClass => $iconLabel)
                            <option value="{{ $iconClass }}" 
                                {{ old('icon', $category->icon ?? '') === $iconClass ? 'selected' : '' }}>
                                {{ $iconLabel }} ({{ $iconClass }})
                            </option>
                        @endforeach
                    </select>
                    @error('icon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Preview Icon --}}
                <div class="mb-4" id="icon-preview" style="display: none;">
                    <label class="form-label fw-semibold">Preview Icon</label>
                    <div class="p-3 bg-light rounded text-center">
                        <i id="preview-icon" class="fas fa-2x text-primary"></i>
                        <p class="mb-0 mt-2 text-muted small" id="preview-label"></p>
                    </div>
                </div>


                {{-- Buttons --}}
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> {{ isset($category) ? 'Perbarui' : 'Simpan' }}
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconSelect = document.getElementById('icon');
    const iconPreview = document.getElementById('icon-preview');
    const previewIcon = document.getElementById('preview-icon');
    const previewLabel = document.getElementById('preview-label');

    function updatePreview() {
        const selectedOption = iconSelect.options[iconSelect.selectedIndex];
        if (iconSelect.value) {
            iconPreview.style.display = 'block';
            previewIcon.className = 'fas ' + iconSelect.value + ' fa-2x text-primary';
            previewLabel.textContent = selectedOption.text;
        } else {
            iconPreview.style.display = 'none';
        }
    }

    iconSelect.addEventListener('change', updatePreview);
    updatePreview(); // Initial call
});
</script>
@endsection
