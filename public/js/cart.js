/* File: public/js/cart.js */
/* REFACORTED: Server-Side Session Cart */

// Helper: Format Rupiah
function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
}

// Helper: Get CSRF Token
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content;
}

// -------------------------------------------------------------------
// FUNGSI UTAMA (AJAX)
// -------------------------------------------------------------------

async function getCart() {
    try {
        const response = await fetch('/cart/data');
        const data = await response.json();
        return data.items || [];
    } catch (error) {
        console.error('Error fetching cart:', error);
        return [];
    }
}

async function addToCart(productId, name, price, quantity = 1, maxStock = null) {
    // Note: name, price, maxStock are ignored by backend (security), but kept for signature compatibility
    // productId can be numeric ID for product or 'bundle_123' for bundles

    try {
        const response = await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                id: String(productId),
                qty: parseInt(quantity)
            })
        });

        const result = await response.json();

        if (response.ok) {
            updateCartIcon(); // Refresh badge

            Swal.fire({
                title: 'Berhasil Ditambahkan!',
                html: `<strong>${name}</strong> telah masuk ke keranjang belanja Anda.`,
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-shopping-cart me-1"></i> Lihat Keranjang',
                cancelButtonText: 'Lanjut Belanja',
                reverseButtons: true
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location.href = '/cart';
                }
            });
        } else {
            Swal.fire({
                title: 'Gagal Menambahkan',
                text: result.message || 'Terjadi kesalahan.',
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        Swal.fire({
            title: 'Error',
            text: 'Gagal terhubung ke server.',
            icon: 'error'
        });
    }
}

async function removeFromCart(productId) {
    try {
        const response = await fetch('/cart/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ id: String(productId) })
        });

        if (response.ok) {
            // Update UI if on cart page
            if (typeof renderCartModern === 'function') {
                renderCartModern();
            }
            // Or reload/update icon
            updateCartIcon();
        }
    } catch (error) {
        console.error('Error removing item:', error);
    }
}

async function updateCartIcon() {
    try {
        const response = await fetch('/cart/data');
        const data = await response.json();
        const count = data.total_qty || 0;

        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.innerText = count;
            if (count > 0) {
                cartCountElement.style.display = 'inline-block';
                cartCountElement.classList.remove('d-none');
            } else {
                cartCountElement.style.display = 'none';
            }
        }
    } catch (error) {
        console.error('Error updating cart icon:', error);
    }
}

// -------------------------------------------------------------------
// EVENT LISTENERS
// -------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', function () {
    updateCartIcon();

    // Event Delegation for Add to Cart buttons
    document.body.addEventListener('click', function (e) {
        const button = e.target.closest('.add-to-cart');

        if (button && !button.hasAttribute('disabled')) {
            e.preventDefault();

            const productId = button.getAttribute('data-product-id');
            const productPrice = button.getAttribute('data-price');
            const productName = button.getAttribute('data-name');
            const productStock = button.getAttribute('data-stock');

            if (productId) {
                addToCart(productId, productName, productPrice, 1, productStock);
            }
        }
    });
});

// Expose functions globally for Blade templates
window.getCart = getCart;
window.formatRupiah = formatRupiah;
window.updateCartIcon = updateCartIcon;
window.removeFromCart = removeFromCart;
window.addToCart = addToCart;