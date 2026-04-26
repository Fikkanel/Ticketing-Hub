@extends('layouts.admin')

@section('title', isset($bundle) ? 'Edit Bundle' : 'Tambah Bundle')

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.events') }}">Events</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.bundles.index', $event) }}">Bundle</a></li>
                <li class="breadcrumb-item active">{{ isset($bundle) ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">
            <i class="fas fa-{{ isset($bundle) ? 'edit' : 'plus' }} me-2"></i>
            {{ isset($bundle) ? 'Edit Bundle' : 'Tambah Bundle Baru' }}
        </h4>
    </div>

    {{-- Form Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ isset($bundle) ? route('admin.bundles.update', [$event, $bundle]) : route('admin.bundles.store', $event) }}" 
                  method="POST" id="bundle-form">
                @csrf
                @if(isset($bundle))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-lg-6">
                        {{-- Nama Bundle --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Nama Bundle <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $bundle->name ?? '') }}"
                                   placeholder="Contoh: Paket Hemat VIP"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Deskripsi singkat bundle...">{{ old('description', $bundle->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                {{-- Harga Bundle --}}
                                <div class="mb-3">
                                    <label for="price" class="form-label fw-semibold">Harga Bundle <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" 
                                               class="form-control @error('price') is-invalid @enderror" 
                                               id="price" 
                                               name="price" 
                                               value="{{ old('price', $bundle->price ?? 0) }}"
                                               min="0"
                                               required>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                {{-- Stok --}}
                                <div class="mb-3">
                                    <label for="stok" class="form-label fw-semibold">Stok <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('stok') is-invalid @enderror" 
                                           id="stok" 
                                           name="stok" 
                                           value="{{ old('stok', $bundle->stok ?? 0) }}"
                                           min="0"
                                           required>
                                    @error('stok')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Status Aktif --}}
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $bundle->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Bundle Aktif
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        {{-- Produk dalam Bundle --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Isi Bundle <span class="text-danger">*</span></label>
                            <div class="alert alert-info small py-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Pilih produk dan jumlah yang termasuk dalam bundle ini.
                            </div>
                            
                            <div id="product-list">
                                @if(isset($bundle) && $bundle->items->count() > 0)
                                    @foreach($bundle->items as $index => $item)
                                        <div class="product-row mb-2" data-index="{{ $index }}">
                                            <div class="row g-2">
                                                <div class="col-7">
                                                    <select name="products[{{ $index }}][product_id]" class="form-select form-select-sm product-select" required>
                                                        <option value="">-- Pilih Produk --</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->product_id }}" 
                                                                    data-price="{{ $product->harga }}"
                                                                    {{ $item->product_id == $product->product_id ? 'selected' : '' }}>
                                                                {{ $product->nama_produk }} (Rp {{ number_format($product->harga, 0, ',', '.') }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-3">
                                                    <input type="number" name="products[{{ $index }}][quantity]" 
                                                           class="form-control form-control-sm qty-input" 
                                                           placeholder="Qty" min="1" value="{{ $item->quantity }}" required>
                                                </div>
                                                <div class="col-2">
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-product" {{ $loop->first && $bundle->items->count() == 1 ? 'disabled' : '' }}>
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="product-row mb-2" data-index="0">
                                        <div class="row g-2">
                                            <div class="col-7">
                                                <select name="products[0][product_id]" class="form-select form-select-sm product-select" required>
                                                    <option value="">-- Pilih Produk --</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->product_id }}" data-price="{{ $product->harga }}">
                                                            {{ $product->nama_produk }} (Rp {{ number_format($product->harga, 0, ',', '.') }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <input type="number" name="products[0][quantity]" 
                                                       class="form-control form-control-sm qty-input" 
                                                       placeholder="Qty" min="1" value="1" required>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-product" disabled>
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button type="button" id="add-product" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fas fa-plus me-1"></i> Tambah Produk
                            </button>
                        </div>

                        {{-- Summary --}}
                        <div class="card bg-light border-0 mt-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Ringkasan</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Harga Satuan:</span>
                                    <span id="total-original">Rp 0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Harga Bundle:</span>
                                    <span id="bundle-price-display" class="text-success fw-bold">Rp 0</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Hemat:</span>
                                    <span id="savings" class="fw-bold text-danger">Rp 0 (0%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> {{ isset($bundle) ? 'Perbarui' : 'Simpan' }}
                    </button>
                    <a href="{{ route('admin.bundles.index', $event) }}" class="btn btn-outline-secondary">
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
    let productIndex = {{ isset($bundle) ? $bundle->items->count() : 1 }};
    const productList = document.getElementById('product-list');
    const addProductBtn = document.getElementById('add-product');
    const priceInput = document.getElementById('price');

    // Product template
    const productTemplate = `
        <div class="product-row mb-2" data-index="INDEX">
            <div class="row g-2">
                <div class="col-7">
                    <select name="products[INDEX][product_id]" class="form-select form-select-sm product-select" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->product_id }}" data-price="{{ $product->harga }}">
                                {{ $product->nama_produk }} (Rp {{ number_format($product->harga, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-3">
                    <input type="number" name="products[INDEX][quantity]" 
                           class="form-control form-control-sm qty-input" 
                           placeholder="Qty" min="1" value="1" required>
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-product">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    // Add product row
    addProductBtn.addEventListener('click', function() {
        const newRow = productTemplate.replace(/INDEX/g, productIndex);
        productList.insertAdjacentHTML('beforeend', newRow);
        productIndex++;
        updateRemoveButtons();
        calculateTotals();
    });

    // Remove product row (event delegation)
    productList.addEventListener('click', function(e) {
        if (e.target.closest('.remove-product')) {
            e.target.closest('.product-row').remove();
            updateRemoveButtons();
            calculateTotals();
        }
    });

    // Update calculations on change
    productList.addEventListener('change', calculateTotals);
    productList.addEventListener('input', calculateTotals);
    priceInput.addEventListener('input', calculateTotals);

    function updateRemoveButtons() {
        const rows = productList.querySelectorAll('.product-row');
        rows.forEach((row, index) => {
            const btn = row.querySelector('.remove-product');
            btn.disabled = rows.length <= 1;
        });
    }

    function calculateTotals() {
        let totalOriginal = 0;
        const rows = productList.querySelectorAll('.product-row');
        
        rows.forEach(row => {
            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.qty-input');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const price = parseFloat(selectedOption.dataset.price) || 0;
                const qty = parseInt(qtyInput.value) || 0;
                totalOriginal += price * qty;
            }
        });

        const bundlePrice = parseFloat(priceInput.value) || 0;
        const savings = totalOriginal - bundlePrice;
        const savingsPercent = totalOriginal > 0 ? Math.round((savings / totalOriginal) * 100) : 0;

        document.getElementById('total-original').textContent = 'Rp ' + totalOriginal.toLocaleString('id-ID');
        document.getElementById('bundle-price-display').textContent = 'Rp ' + bundlePrice.toLocaleString('id-ID');
        document.getElementById('savings').textContent = 'Rp ' + savings.toLocaleString('id-ID') + ' (' + savingsPercent + '%)';
    }

    // Initial calculation
    calculateTotals();
});
</script>
@endsection
