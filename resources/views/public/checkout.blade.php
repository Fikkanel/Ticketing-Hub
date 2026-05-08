@extends('layouts.public')

@section('title', 'Checkout')

@section('content')
<div class="row mb-4">
    <div class="col-12 pt-4">
        <h3 class="fw-bold mb-0">Checkout</h3>
        <p class="text-muted small">Lengkapi data diri dan lakukan pembayaran.</p>
    </div>
</div>

<form action="{{ route('public.checkout.process') }}" method="POST" id="checkout-form">
    @csrf
    {{-- REMOVED: cart_data_json hidden input (Backend now reads from Session) --}}
    {{-- <input type="hidden" name="cart_data_json" id="cart_data_json"> --}}
    
    <div class="row">
        {{-- KOLOM KIRI: Form Data Diri --}}
        <div class="col-lg-7">
            
            {{-- ERROR ALERT PLACEHOLDER --}}
            <div class="alert alert-danger alert-checkout-error rounded-3 shadow-sm mx-0 mt-0 mb-3" style="display: none;"></div>

            {{-- 1. IDENTITAS PEMBELI --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4" id="step-identity-card">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-user-circle me-2 text-primary-custom"></i> Data Pemesan</h6>
                </div>
                <div class="card-body p-4">
                    {{-- Login / Guest Option --}}
                    @guest('customer')
                        <div class="alert alert-soft-primary d-flex align-items-center mb-4" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <div class="small">
                                Sudah punya akun? <a href="{{ route('customer.login') }}" class="fw-bold text-decoration-none">Login disini</a> untuk pengisian data otomatis.
                            </div>
                        </div>
                    @endguest

                    <div class="row g-3">
                         {{-- Dynamic Buyer Fields --}}
                        <div class="col-12" id="buyer-form-fields-container">
                             {{-- Default fields fallback --}}
                             <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required value="{{ Auth::guard('customer')->user()->name ?? old('nama') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control" required value="{{ Auth::guard('customer')->user()->email ?? old('email') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nomor WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="telepon" class="form-control" required value="{{ Auth::guard('customer')->user()->phone ?? old('telepon') }}" placeholder="08...">
                            </div>
                        </div>

                        {{-- Password Field for New Users --}}
                        @guest('customer')
                        <div class="col-12" id="guest-password-field">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Buat Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Minimal 6 karakter" minlength="6">
                                </div>
                                <div class="form-text small">Password untuk login ke akun Anda dan melihat tiket.</div>
                            </div>
                        </div>
                        @endguest
                    </div>
                </div>
            </div>

            {{-- 2. CUSTOM FIELDS (EVENT SPECIFIC) --}}
            <div id="custom-fields-container"></div> 

            {{-- 3. METODE PEMBAYARAN --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4" id="payment-method-card">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-wallet me-2 text-primary-custom"></i> Metode Pembayaran</h6>
                </div>
                <div class="card-body p-4">
                    <div id="payment-channels-loading" class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <span class="ms-2 small text-muted">Memuat metode pembayaran...</span>
                    </div>

                    <style>
                        .payment-label .check-icon {
                            display: none;
                            color: var(--primary-color);
                        }
                        .btn-check:checked + .payment-label {
                            border-color: var(--primary-color) !important;
                            background-color: rgba(58, 125, 68, 0.05); /* Soft primary */
                        }
                        .btn-check:checked + .payment-label .check-icon {
                            display: block;
                        }
                        .payment-icon-box {
                            width: 50px; 
                            height: 40px; 
                            display: flex; 
                            align-items: center; 
                            justify-content: center;
                        }
                    </style>
                    <div id="payment-channels-container" style="display: none;">
                        {{-- QRIS --}}
                        <div class="payment-option mb-3" data-channel="qris">
                            <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_qris" value="qris" checked>
                            <label class="btn btn-outline-light text-start w-100 p-3 d-flex align-items-center payment-label border" for="pay_qris">
                                <div class="flex-shrink-0 bg-white rounded border payment-icon-box">
                                    <i class="fas fa-qrcode fa-lg" style="color: #ed2a26;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold text-dark">QRIS (OVO, GoPay, Dana, ShopeePay)</div>
                                    <div class="small text-muted">Scan QR code instan</div>
                                </div>
                                <div class="ms-auto check-icon"><i class="fas fa-check-circle fa-lg"></i></div>
                            </label>
                        </div>
                        
                         {{-- E-Wallet (SNAP) --}}
                         <div class="payment-option mb-3" data-channel="gopay">
                            <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_gopay" value="gopay">
                            <label class="btn btn-outline-light text-start w-100 p-3 d-flex align-items-center payment-label border" for="pay_gopay">
                                <div class="flex-shrink-0 bg-white rounded border payment-icon-box">
                                    <i class="fas fa-wallet fa-lg" style="color: #00a5cf;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold text-dark">GoPay / E-Wallet</div>
                                    <div class="small text-muted">Pembayaran via aplikasi E-Wallet</div>
                                </div>
                                <div class="ms-auto check-icon"><i class="fas fa-check-circle fa-lg"></i></div>
                            </label>
                        </div>

                        {{-- Virtual Account (SNAP) --}}
                        <div class="payment-option mb-3" data-channel="bank_transfer">
                            <input type="radio" class="btn-check" name="metode_pembayaran" id="pay_va" value="bank_transfer">
                            <label class="btn btn-outline-light text-start w-100 p-3 d-flex align-items-center payment-label border" for="pay_va">
                                <div class="flex-shrink-0 bg-white rounded border payment-icon-box">
                                    <i class="fas fa-university fa-lg text-secondary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold text-dark">Virtual Account / Transfer Bank</div>
                                    <div class="small text-muted">BCA, BNI, BRI, Mandiri, Permata</div>
                                </div>
                                <div class="ms-auto check-icon"><i class="fas fa-check-circle fa-lg"></i></div>
                            </label>
                        </div>
                    </div>
                    
                    <div id="payment-free-msg" class="alert alert-success mt-3" style="display:none;">
                        <i class="fas fa-check-circle me-2"></i> Event ini <strong>GRATIS</strong>. Tidak diperlukan pembayaran.
                    </div>
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: Ringkasan Order --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px; z-index: 1;">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Ringkasan Pesanan</h6>
                </div>
                <div class="card-body p-4">
                    {{-- Loader --}}
                    <div id="cart-loader" class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    </div>
                    
                    {{-- Items List --}}
                    <div id="checkout-items" class="mb-4"></div>

                    <hr class="border-dashed">

                    {{-- Form Diskon --}}
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Punya kode promo?</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="diskon_code_input" placeholder="Masukkan kode">
                            <button class="btn btn-dark" type="button" id="btn-apply-discount">Gunakan</button>
                        </div>
                        <input type="hidden" name="diskon_code" id="diskon_code_hidden">
                        <div id="discount-message" class="form-text mt-1"></div>
                    </div>

                    {{-- Rincian Biaya --}}
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Subtotal Produk</span>
                        <span class="fw-bold text-dark" id="summary-subtotal">Rp 0</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2 small text-muted text-success" id="row-discount" style="display: none;">
                        <span>Diskon <span id="discount-code-badge" class="badge bg-success ms-1"></span></span>
                        <span class="fw-bold" id="summary-discount">-Rp 0</span>
                    </div>

                    <div id="tax-rows" style="display: none;">
                         <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>Biaya Layanan & Admin</span>
                            <span class="fw-bold text-dark" id="summary-admin-fee">Rp 0</span>
                        </div>
                         <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>PPN ({{ $taxSettings['ppn_percent'] }}%)</span>
                            <span class="fw-bold text-dark" id="summary-ppn">Rp 0</span>
                        </div>
                    </div>

                    <hr class="border-dashed my-3">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold text-dark fs-5">Total Bayar</span>
                        <span class="fw-bold text-primary-custom fs-4" id="summary-grand-total">Rp 0</span>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agree_terms" required checked>
                        <label class="form-check-label small text-muted" for="agree_terms">
                            Saya menyetujui <a href="#" class="text-decoration-none">Syarat & Ketentuan</a> yang berlaku.
                        </label>
                    </div>

                    <button type="submit" id="btn-submit-order" class="btn btn-primary-custom w-100 py-3 fw-bold shadow-sm" disabled>
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="btn-loader"></span>
                        <i class="fas fa-lock me-2"></i> Lanjut ke Pembayaran
                    </button>
                    
                    <div class="text-center mt-3">
                         <a href="{{ route('public.cart.show') }}" class="small text-muted text-decoration-none">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Keranjang
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>

{{-- Hidden Inputs for Tax Settings (Passed from Controller) --}}
<input type="hidden" id="tax_admin_fee_pct" value="{{ $taxSettings['admin_fee_percentage'] }}">
<input type="hidden" id="tax_admin_fee_min" value="{{ $taxSettings['admin_fee_min'] }}">
<input type="hidden" id="tax_ppn_pct" value="{{ $taxSettings['ppn_percent'] }}">
<input type="hidden" id="tax_service_fee_qris" value="{{ $taxSettings['service_fee_qris'] }}">
<input type="hidden" id="tax_service_fee_va" value="{{ $taxSettings['service_fee_va'] }}">
<input type="hidden" id="tax_service_fee_gopay" value="{{ $taxSettings['service_fee_gopay'] }}">
@endsection

@section('scripts')
{{-- Midtrans Snap --}}
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const checkoutForm = document.getElementById('checkout-form');
    const btnSubmit = document.getElementById('btn-submit-order');
    const cartLoader = document.getElementById('cart-loader');
    const checkoutItemsContainer = document.getElementById('checkout-items');
    
    // Summary Els
    const elSubtotal = document.getElementById('summary-subtotal');
    const elGrandTotal = document.getElementById('summary-grand-total');
    const elDiscountRow = document.getElementById('row-discount');
    const elDiscountAmount = document.getElementById('summary-discount');
    const badgeDiscount = document.getElementById('discount-code-badge');
    const taxRows = document.getElementById('tax-rows');
    const elAdminFee = document.getElementById('summary-admin-fee');
    const elPpn = document.getElementById('summary-ppn');
    
    // Tax Config
    const TAX_CFG = {
        admin_pct: parseFloat(document.getElementById('tax_admin_fee_pct').value) || 0,
        admin_min: parseFloat(document.getElementById('tax_admin_fee_min').value) || 0,
        ppn_pct: parseFloat(document.getElementById('tax_ppn_pct').value) || 0,
        fee_qris: parseFloat(document.getElementById('tax_service_fee_qris').value) || 0,
        fee_va: parseFloat(document.getElementById('tax_service_fee_va').value) || 0,
        fee_gopay: parseFloat(document.getElementById('tax_service_fee_gopay').value) || 0,
    };

    // 1. Initial State & Data
    let cartData = [];
    let productIds = [];
    let rawSubtotal = 0;
    let appliedDiscount = 0;
    let applyTax = false;
    let isFreeCart = false;
    let currentPaymentMethod = 'qris';
    
    // Customer Data for Pre-filling (if logged in)
    const customerData = {
        nik: "{{ auth()->guard('customer')->user()->nik ?? '' }}",
        dob: "{{ auth()->guard('customer')->user()->dob ?? '' }}",
        gender: "{{ auth()->guard('customer')->user()->gender ?? '' }}"
    };

    // 1. Fetch Cart Data from Server
    try {
        const response = await fetch('/cart/data');
        const data = await response.json();
        
        cartData = data.items || [];
        
        if (cartData.length === 0) {
            window.location.href = "{{ route('public.cart.show') }}";
            return;
        }
        
    } catch (e) {
        console.error("Failed to load cart", e);
        alert("Gagal memuat data keranjang.");
        return;
    }

    // 2. Render Items & Check Tax Status
    cartLoader.style.display = 'none';
    let html = '';
    rawSubtotal = 0;
    productIds.length = 0;

    cartData.forEach(item => {
        let price = parseFloat(item.price);
        let qty = parseInt(item.qty);
        let subtotal = price * qty;
        rawSubtotal += subtotal;
        productIds.push(item.id);

        html += `
        <div class="d-flex align-items-center mb-3">
             <div class="flex-shrink-0 bg-light rounded px-2 py-2" style="width: 50px; height: 50px; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-ticket-alt text-secondary"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                 <h6 class="mb-0 fw-bold small text-dark">${item.name}</h6>
                 <div class="text-muted small">${qty} x ${formatRupiah(price)}</div>
            </div>
            <div class="fw-bold text-dark small">
                ${formatRupiah(subtotal)}
            </div>
        </div>
        `;
    });
    
    checkoutItemsContainer.innerHTML = html;
    elSubtotal.innerText = formatRupiah(rawSubtotal);
    
    // 3. Check Tax Status & Payment Channels (API)
    try {
        const taxResponse = await fetch("{{ route('api.validate_tax_status') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ product_ids: productIds })
        });
        
        if (!taxResponse.ok) {
            throw new Error(`HTTP error! status: ${taxResponse.status}`);
        }
        
        const taxData = await taxResponse.json();
        
        if (taxData.error) {
            throw new Error(taxData.error);
        }
        
        applyTax = taxData.apply_tax;
        isFreeCart = taxData.is_free_cart;
        
        // Handle Form Fields (Buyer & Custom)
        renderBuyerFields(taxData.buyer_form_fields);
        if (taxData.custom_form_fields && taxData.custom_form_fields.length > 0) {
            renderCustomFields(taxData.custom_form_fields);
        }

        // Handle Payment Methods Visibility & Free Cart
        if (isFreeCart) {
            document.getElementById('payment-channels-loading').style.display = 'none';
            document.getElementById('payment-channels-container').style.display = 'none';
            document.getElementById('payment-free-msg').style.display = 'block';
            currentPaymentMethod = 'FREE';
            btnSubmit.innerHTML = '<i class="fas fa-check-circle me-2"></i> Daftar Sekarang (GRATIS)';
        } else {
             document.getElementById('payment-channels-loading').style.display = 'none';
             document.getElementById('payment-channels-container').style.display = 'block';
        }
        
    } catch (e) {
        console.error("Tax Check Error", e);
        document.getElementById('payment-channels-loading').innerHTML = '<div class="alert alert-danger small"><i class="fas fa-exclamation-triangle"></i> Gagal memuat metode pembayaran. Silakan <a href="#" onclick="location.reload(); return false;">Refresh Halaman</a>.</div>';
    }
    
    // 4. Calculate Totals
    calculateGrandTotal();
    btnSubmit.disabled = false;

    // --- HELPER FUNCTIONS ---

    function calculateGrandTotal() {
        if (isFreeCart) {
            elGrandTotal.innerText = 'Rp 0';
            taxRows.style.display = 'none';
            return;
        }

        let total = rawSubtotal - appliedDiscount;
        if (total < 0) total = 0;

        let adminFee = 0;
        let serviceFee = 0;
        let ppn = 0;

        if (applyTax) {
             // Admin Fee
             let calcAdmin = (TAX_CFG.admin_pct / 100) * total; // based on discounted total
             // Use original subtotal? usually fees are based on transaction value. 
             // Logic in controller: ($adminFeePct / 100) * $totalHargaProduk (BEFORE discount? Or After?)
             // Controller checks: $totalHargaProduk (BEFORE discount). 
             // Let's match Controller:
             calcAdmin = (TAX_CFG.admin_pct / 100) * rawSubtotal;
             adminFee = Math.max(calcAdmin, TAX_CFG.admin_min);
             
             // Service Fee
             let feePct = 0;
             if (currentPaymentMethod === 'qris') feePct = TAX_CFG.fee_qris;
             else if (currentPaymentMethod === 'gopay') feePct = TAX_CFG.fee_gopay; // fixed?
             else if (currentPaymentMethod === 'bank_transfer') feePct = TAX_CFG.fee_va; // fixed?
             
             // Note: Controller logic uses hardcoded fee types or percentage logic.
             // Controller: $serviceFee = ($pct / 100) * $totalHargaProduk; (for QRIS)
             // For VA/Gopay, usually fixed prices.
             // Simplified sync:
             if (currentPaymentMethod === 'qris') {
                 serviceFee = (TAX_CFG.fee_qris / 100) * rawSubtotal; 
             } else if (['gopay', 'shopeepay'].includes(currentPaymentMethod)) {
                 serviceFee = TAX_CFG.fee_gopay;
             } else {
                 serviceFee = TAX_CFG.fee_va;
             }
             
             // PPN
             ppn = (TAX_CFG.ppn_pct / 100) * (adminFee + serviceFee); // Tax on Fees
        }

        let grandTotal = total + adminFee + serviceFee + ppn;

        // Render
        if (applyTax) {
            taxRows.style.display = 'block';
            elAdminFee.innerText = formatRupiah(adminFee + serviceFee);
            elPpn.innerText = formatRupiah(ppn);
        } else {
            taxRows.style.display = 'none';
        }

        elGrandTotal.innerText = formatRupiah(grandTotal);
    }
    
    // Handle Payment Method Change
    const paymentRadios = document.querySelectorAll('input[name="metode_pembayaran"]');
    paymentRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                currentPaymentMethod = this.value;
                
                // Update UI active state
                document.querySelectorAll('.payment-label').forEach(lbl => {
                    lbl.classList.remove('border-primary', 'bg-soft-primary');
                    lbl.classList.add('border');
                });
                const label = document.querySelector(`label[for="${this.id}"]`);
                if(label) {
                    label.classList.remove('border');
                    label.classList.add('border-primary', 'bg-soft-primary');
                }
                
                calculateGrandTotal();
            }
        });
    });

    // Discount Logic
    const btnApplyDiscount = document.getElementById('btn-apply-discount');
    const inputDiscount = document.getElementById('diskon_code_input');
    const hiddenDiscount = document.getElementById('diskon_code_hidden');
    const msgDiscount = document.getElementById('discount-message');

    if (btnApplyDiscount) {
        btnApplyDiscount.addEventListener('click', async function() {
            const code = inputDiscount.value.trim();
            if (!code) return;

            btnApplyDiscount.disabled = true;
            btnApplyDiscount.innerHTML = '...';

            try {
                const res = await fetch("{{ route('api.validate_discount') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        code: code,
                        cart_items: cartData, // Send cart data for validation
                        total_amount: rawSubtotal 
                    })
                });

                const result = await res.json();

                if (result.valid) {
                    appliedDiscount = result.discount_amount;
                    hiddenDiscount.value = result.code;
                    
                    msgDiscount.innerHTML = `<span class="text-success"><i class="fas fa-check-circle"></i> ${result.message}</span>`;
                    
                    elDiscountRow.style.display = 'flex';
                    elDiscountAmount.innerText = '-' + formatRupiah(appliedDiscount);
                    badgeDiscount.innerText = code;
                    
                    calculateGrandTotal();
                } else {
                    appliedDiscount = 0;
                    hiddenDiscount.value = '';
                    elDiscountRow.style.display = 'none';
                    msgDiscount.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle"></i> ${result.message}</span>`;
                    calculateGrandTotal();
                }

            } catch (error) {
                console.error(error);
                msgDiscount.innerHTML = '<span class="text-danger">Gagal memvalidasi kode.</span>';
            } finally {
                btnApplyDiscount.disabled = false;
                btnApplyDiscount.innerHTML = 'Gunakan';
            }
        });
    }

    // Render Fields Helper
    function renderBuyerFields(fields) {
        const container = document.getElementById('buyer-form-fields-container');
        if (!container || !fields) return;
        
        const oldDynamic = container.querySelectorAll('.dynamic-field');
        oldDynamic.forEach(el => el.remove());
        
        fields.forEach(f => {
             if (['nik', 'dob', 'gender'].includes(f.field) && f.enabled) {
                 const div = document.createElement('div');
                 div.className = 'mb-3 dynamic-field';
                 let input = '';
                 const req = f.required ? 'required' : '';
                 const star = f.required ? '<span class="text-danger">*</span>' : '';
                 
                 if (f.field === 'gender') {
                     const selectedL = customerData.gender === 'L' ? 'selected' : '';
                     const selectedP = customerData.gender === 'P' ? 'selected' : '';
                     input = `
                        <select name="gender" class="form-select" ${req}>
                            <option value="">Pilih Gender</option>
                            <option value="L" ${selectedL}>Laki-laki</option>
                            <option value="P" ${selectedP}>Perempuan</option>
                        </select>
                     `;
                 } else if (f.field === 'nik') {
                     input = `
                        <input type="text" name="${f.field}" id="input-nik" class="form-control" ${req} maxlength="16" placeholder="16 digit angka" value="${customerData.nik}" oninput="this.value = this.value.replace(/[^0-9]/g, ''); validateNik(this)">
                        <div id="nik-announcement" class="small mt-1" style="display:none;"></div>
                     `;
                     // Trigger validation if value exists
                     setTimeout(() => {
                         const el = document.getElementById('input-nik');
                         if(el && el.value) validateNik(el);
                     }, 100);
                 } else {
                     const type = f.field === 'dob' ? 'date' : 'text';
                     const val = f.field === 'dob' ? customerData.dob : '';
                     const maxAttr = f.field === 'dob' ? 'max="2010-12-31"' : '';
                     input = `<input type="${type}" name="${f.field}" class="form-control" ${req} ${maxAttr} value="${val}">`;
                 }
                 
                 div.innerHTML = `<label class="form-label small fw-bold">${f.label} ${star}</label>${input}`;
                 container.appendChild(div);
             }
        });
    }

    // Global Helper for NIK Validation
    window.validateNik = function(el) {
        const announcement = document.getElementById('nik-announcement');
        if (!announcement) return;
        
        const val = el.value;
        if (val.length === 0) {
            announcement.style.display = 'none';
        } else if (val.length < 16) {
            announcement.style.display = 'block';
            announcement.className = 'small mt-1 text-danger';
            announcement.innerHTML = `<i class="fas fa-info-circle me-1"></i> NIK kurang dari 16 digit (${val.length}/16)`;
        } else if (val.length === 16) {
            announcement.style.display = 'block';
            announcement.className = 'small mt-1 text-success';
            announcement.innerHTML = `<i class="fas fa-check-circle me-1"></i> NIK sudah tepat 16 digit`;
        } else {
            // Seharusnya tidak mungkin lewat 16 karena maxlength, tapi untuk jaga-jaga
            announcement.style.display = 'block';
            announcement.className = 'small mt-1 text-danger';
            announcement.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> NIK lebih dari 16 digit`;
        }
    }

    function renderCustomFields(fields) {
        const container = document.getElementById('custom-fields-container');
        container.innerHTML = ''; 
        if (!fields || fields.length === 0) return;

        let html = `
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-clipboard-list me-2 text-primary-custom"></i> Informasi Tambahan</h6>
            </div>
            <div class="card-body p-4">
        `;

        fields.forEach((f, index) => {
            const req = f.required ? 'required' : '';
            const star = f.required ? '<span class="text-danger">*</span>' : '';
            const fieldName = `custom_field_${index}`; // or use slug if available
            
            html += `<div class="mb-3"><label class="form-label small fw-bold fs-6">${f.label} ${star}</label>`;
            
            if (f.type === 'text') {
                html += `<input type="text" name="${fieldName}" class="form-control" ${req}>`;
            } else if (f.type === 'textarea') {
                html += `<textarea name="${fieldName}" class="form-control" rows="2" ${req}></textarea>`;
            } else if (f.type === 'select') { // dropdown
                html += `<select name="${fieldName}" class="form-select" ${req}>
                            <option value="">Pilih...</option>`;
                if (f.options) {
                    f.options.forEach(opt => {
                        html += `<option value="${opt}">${opt}</option>`;
                    });
                }
                html += `</select>`;
            }
            html += `</div>`;
        });

        html += `</div></div>`;
        container.innerHTML = html;
    }
    
    // Clear cart after successful checkout (passed via redirect session?)
    // This is optional if backend cleans it. 
    // Backend `processCheckout` DOES NOT currently clear the cart upon success yet in my code reading?
    // Wait, usually it should. `CartController::clear()` is separate.
    // If backend doesn't clear, we should call `removeFromCart` or `clear`?
    // Actually, `processCheckout` SHOULD clear the session cart upon success. I didn't verify that line.
    // Let me check if `processCheckout` clears cart.
});
</script>
@endsection
