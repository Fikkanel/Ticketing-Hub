@extends('layouts.public')

@section('title', 'Keranjang Belanja')

@section('content')
    <div class="row mb-4">
        <div class="col-12 pt-4">
            <h3 class="fw-bold mb-0">Keranjang Belanja</h3>
            <p class="text-muted small">Kelola item travel kit dan tiket event Anda.</p>
        </div>
    </div>

    <div class="row">
        {{-- KOLOM KIRI: Daftar Item --}}
        <div class="col-lg-8">
            {{-- State Kosong --}}
            <div id="cart-empty" class="text-center py-5" style="display: none;">
                <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-cart-2130356-1800917.png" alt="Empty Cart" style="width: 200px; opacity: 0.7;">
                <h5 class="fw-bold mt-3">Keranjang Masih Kosong</h5>
                <p class="text-muted">Yuk, cari event seru dan travel kit menarik!</p>
                <a href="{{ route('public.index') }}" class="btn btn-primary-custom mt-2">Jelajahi Event</a>
            </div>
            
            {{-- Container Item (Akan diisi oleh Javascript) --}}
            <div id="cart-items-container">
                 {{-- Loading Spinner --}}
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- KOLOM KANAN: Ringkasan --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Ringkasan Belanja</h5>
                    
                    <div class="d-flex justify-content-between mb-2 small text-muted">
                        <span>Total Item</span>
                        <span id="total-qty" class="fw-bold text-dark">0</span>
                    </div>
                    
                    <hr class="border-dashed my-3">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fw-bold text-muted">Subtotal</span>
                        <span id="grand-total" class="fs-4 fw-bold text-primary-custom">Rp 0</span>
                    </div>
                    
                    <a href="{{ route('public.checkout') }}" id="checkout-button" class="btn btn-primary-custom w-100 py-3 fw-bold shadow-sm" style="display: none;">
                        Lanjut ke Pembayaran <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                    
                    <a href="{{ route('public.index') }}" class="btn btn-outline-secondary w-100 mt-2 border-0">
                        Tambah Produk Lain
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- KONFIGURASI & ELEMEN ---
        const container = document.getElementById('cart-items-container');
        const emptyState = document.getElementById('cart-empty');
        const totalQtyEl = document.getElementById('total-qty');
        const grandTotalEl = document.getElementById('grand-total');
        const checkoutBtn = document.getElementById('checkout-button');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // --- CORE FUNCTION: RENDER ---
        async function renderCart() {
            try {
                const items = await getCart(); // Use global function from cart.js (calls /cart/data)
                
                // 1. Cek Jika Kosong
                if (items.length === 0) {
                    container.innerHTML = '';
                    emptyState.style.display = 'block';
                    if(checkoutBtn) checkoutBtn.style.display = 'none';
                    totalQtyEl.innerText = 0;
                    grandTotalEl.innerText = 'Rp 0';
                    return;
                }

                // 2. Jika Ada Isi
                emptyState.style.display = 'none';
                if(checkoutBtn) checkoutBtn.style.display = 'block';

                let html = '';
                let totalQty = 0;
                let totalPrice = 0;

                items.forEach((item) => {
                    let qty = parseInt(item.qty) || 1;
                    let price = parseFloat(item.price) || 0;
                    let itemId = String(item.id); 
                    
                    let subtotal = price * qty;
                    totalQty += qty;
                    totalPrice += subtotal;

                    html += `
                    <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                {{-- Icon Placeholder --}}
                                <div class="flex-shrink-0 bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-ticket-alt fa-lg text-secondary"></i>
                                </div>
                                
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold mb-1 text-dark">${item.name}</h6>
                                    <div class="text-primary-custom fw-bold small">${formatRupiah(price)}</div>
                                </div>

                                {{-- Kontrol Kuantitas --}}
                                <div class="d-flex align-items-center bg-light rounded-pill px-2 py-1 border mx-3">
                                    <button type="button" class="btn btn-sm btn-link text-dark p-0 text-decoration-none btn-update-qty" 
                                        data-id="${itemId}" data-qty="${qty - 1}" data-action="decrease" ${qty <= 1 ? 'disabled' : ''}>
                                        <i class="fas fa-minus small pointer-events-none"></i>
                                    </button>
                                    
                                    <span class="mx-3 fw-bold small" style="min-width: 20px; text-align: center;">${qty}</span>
                                    
                                    <button type="button" class="btn btn-sm btn-link text-dark p-0 text-decoration-none btn-update-qty" 
                                        data-id="${itemId}" data-qty="${qty + 1}" data-action="increase">
                                        <i class="fas fa-plus small pointer-events-none"></i>
                                    </button>
                                </div>
                                
                                <button type="button" class="btn btn-link text-danger btn-remove-item" 
                                    data-id="${itemId}" title="Hapus Item">
                                    <i class="fas fa-trash-alt pointer-events-none"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    `;
                });

                container.innerHTML = html;
                totalQtyEl.innerText = totalQty;
                grandTotalEl.innerText = formatRupiah(totalPrice);
                
            } catch(e) {
                console.error("Failed to render cart:", e);
                container.innerHTML = '<div class="alert alert-danger">Gagal memuat keranjang.</div>';
            }
        }

        // --- ACTION HANDLERS ---

        async function updateQty(id, qty) {
            try {
                const response = await fetch("{{ route('cart.update') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({ id: id, qty: qty })
                });

                const result = await response.json();

                if (!response.ok) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: result.message || 'Gagal mengupdate.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                     await renderCart();
                     updateCartIcon();
                }
            } catch (error) {
                 console.error(error);
            }
        }

        async function removeItem(id) {
             Swal.fire({
                title: 'Hapus Item?',
                text: "Item ini akan dihapus dari keranjang Anda.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                     try {
                        const response = await fetch("{{ route('cart.remove') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": csrfToken
                            },
                            body: JSON.stringify({ id: id })
                        });

                        if (response.ok) {
                             await renderCart();
                             updateCartIcon();
                             Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Item berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                     } catch(e) {
                         console.error(e);
                     }
                }
            });
        }

        // --- EVENT DELEGATION ---
        container.addEventListener('click', function(e) {
            // Update Qty
            const btnQty = e.target.closest('.btn-update-qty');
            if (btnQty && !btnQty.disabled) {
                e.preventDefault();
                const id = btnQty.getAttribute('data-id');
                const qty = parseInt(btnQty.getAttribute('data-qty'));
                if (qty > 0) updateQty(id, qty);
                return;
            }

            // Remove Item
            const btnRemove = e.target.closest('.btn-remove-item');
            if (btnRemove) {
                e.preventDefault();
                const id = btnRemove.getAttribute('data-id');
                removeItem(id);
                return;
            }
        });

        // --- INIT ---
        renderCart();
    });
</script>

<style>
    .pointer-events-none {
        pointer-events: none;
    }
</style>
@endsection
