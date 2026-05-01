<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\BannerController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini Anda dapat mendaftarkan route web untuk aplikasi Anda. Route ini 
| dimuat oleh RouteServiceProvider dalam grup yang berisi middleware "web".
|
*/

// =========================================================================
// 1. PUBLIC ROUTES (FRONT-END TIXKITA)
// =========================================================================

// Halaman Utama: Kalender Acara
Route::get('/', [PublicController::class, 'index'])->name('public.index');

// Halaman Utama: Filter berdasarkan Kategori (URL bersih)
Route::get('/category/{slug}', [PublicController::class, 'index'])->name('public.category');

// Halaman Detail Acara (dilindungi Waiting Room saat traffic tinggi)
Route::get('/events/{event_id}', [PublicController::class, 'showEventDetail'])
    ->middleware('waiting_room')
    ->name('public.event.detail');

// Waiting Room (Virtual Queue)
Route::get('/waiting-room/{event_id}', [PublicController::class, 'showWaitingRoom'])->name('public.waiting_room');
Route::get('/waiting-room/status/{event_id}', [PublicController::class, 'checkWaitingStatus'])->name('public.waiting_room.status');

// Halaman Profil Organizer
Route::get('/organizer/{slug}', [PublicController::class, 'showOrganizerProfile'])->name('public.organizer.profile');



// Halaman Keranjang (rate limited: 30 request/menit per user)
Route::prefix('cart')->middleware('throttle:30,1')->group(function () {
    Route::get('/data', [App\Http\Controllers\CartController::class, 'getData'])->name('cart.data');
    Route::post('/add', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
    Route::post('/update', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
    Route::post('/remove', [App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
    Route::post('/clear', [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
});

Route::get('/cart', [PublicController::class, 'showCart'])->name('public.cart.show');

// Proses Checkout (rate limited: 5 request/menit per user - mencegah bot)
Route::get('/checkout', [PublicController::class, 'showCheckoutForm'])->name('public.checkout');
Route::post('/checkout', [PublicController::class, 'processCheckout'])->middleware('throttle:5,1')->name('public.checkout.process');
Route::post('/checkout/check-email', [PublicController::class, 'checkEmail'])->name('public.checkout.check_email');

// Halaman Invoice (Setelah Transaksi Selesai)
Route::get('/invoice/{order_id}', [PublicController::class, 'showInvoice'])->name('public.invoice');

// History (Guest Session)
Route::get('/history', [PublicController::class, 'guestHistory'])->name('public.history');

Route::post('/api/validate-discount', [PublicController::class, 'validateDiscount'])->name('api.validate_discount');
Route::post('/api/validate-tax-status', [PublicController::class, 'checkTaxStatus'])->name('api.validate_tax_status');

// Route Khusus Pembayaran Midtrans
Route::post('/payment/notification', [PublicController::class, 'handlePaymentNotification'])->name('payment.notification');
Route::get('/payment/notification', function () {
    return redirect()->route('public.index'); // Redirect direct access to home
});
// Route::get('/payment/{order_id}', [PublicController::class, 'showPaymentPage'])->name('public.payment_page');

Route::get('/payment-instructions/{order_id}', [PublicController::class, 'showPaymentInstructions'])->name('public.payment_instructions');
// routes/web.php
Route::post('/payment-confirm/{order_id}', [PublicController::class, 'simulatePaymentConfirmation'])->name('public.payment_confirm_simulate');
Route::view('/terms-and-conditions', 'public.terms')->name('public.terms');

// =========================================================================
// 1.5 CUSTOMER AUTH ROUTES
// =========================================================================
use App\Http\Controllers\CustomerAuthController;

// Guest Only (Belum Login)
Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('customer.login.process');
    
    Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register.process');
    

    // Forgot Password Routes
    Route::get('/forgot-password', [CustomerAuthController::class, 'showForgotPasswordForm'])->name('customer.password.request');
    Route::post('/forgot-password', [CustomerAuthController::class, 'sendResetLink'])->name('customer.password.email');
    Route::get('/reset-password', [CustomerAuthController::class, 'showResetForm'])->name('customer.password.reset');
    Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword'])->name('customer.password.update');
});

// Customer Authenticated
Route::middleware('auth:customer')->group(function () {
    Route::get('/dashboard', [CustomerAuthController::class, 'dashboard'])->name('customer.dashboard');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
});

// Alias untuk home
Route::get('/home', function () {
    return redirect()->route('public.index');
})->name('home');

// =========================================================================
// 3. SUBDOMAIN: access.tixkita.id (Sponsorship Ticket Access)
// =========================================================================
Route::domain('access.tixkita.id')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\SponsorshipController::class, 'showTicket'])->name('sponsorship.ticket');
});

// =========================================================================
// 2. ADMIN ROUTES (BACK-END MANAGEMENT)
// =========================================================================

Route::prefix('admin')->group(function () {
    
    // 2.1. LOGIN & LOGOUT (PUBLIC ACCESS)
    Route::get('/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout'); // Tambahkan jika Anda sudah buat metode logout

    // Ubah Password
    Route::get('/change-password', [AdminController::class, 'showChangePasswordForm'])->name('admin.password.form');
    Route::put('/change-password', [AdminController::class, 'updatePassword'])->name('admin.password.update');
    
    // 2.2. AREA TERPROTEKSI (Memerlukan Middleware 'auth')
    Route::middleware('auth')->group(function () {
        
        // DASHBOARD
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        
        // MANAJEMEN EVENT (CUSTOM CRUD)
        Route::get('/events', [AdminController::class, 'manageEvents'])->name('admin.events');
        Route::get('/events/create', [AdminController::class, 'createEvent'])->name('admin.events.create');
        Route::post('/events', [AdminController::class, 'storeEvent'])->name('admin.events.store');
        Route::get('/events/{event_id}/edit', [AdminController::class, 'editEvent'])->name('admin.events.edit'); 
        Route::put('/events/{event_id}', [AdminController::class, 'updateEvent'])->name('admin.events.update');
        Route::delete('/events/{event_id}', [AdminController::class, 'deleteEvent'])->name('admin.events.delete');

        // MANAJEMEN LOKASI (RESOURCE CRUD)
        Route::resource('locations', LocationController::class)->names('admin.locations');
        
        // MANAJEMEN PRODUK (RESOURCE CRUD)
        Route::resource('products', ProductController::class)->names('admin.products');
        Route::get('/products/{product}/seat-layout', [\App\Http\Controllers\Admin\SeatLayoutController::class, 'index'])->name('admin.products.seat_layout');
        Route::post('/products/{product}/seat-layout', [\App\Http\Controllers\Admin\SeatLayoutController::class, 'store'])->name('admin.products.seat_layout.store');
        
        // MANAJEMEN ORDER
        Route::get('/orders', [AdminController::class, 'manageOrders'])->name('admin.orders');
        Route::put('/orders/{order_id}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.orders.update_status');
        Route::post('/orders/{order_id}/resend-ticket', [AdminController::class, 'resendTicket'])->name('admin.orders.resend_ticket');
        
        // MANAJEMEN SPONSORSHIP
        Route::get('/sponsorships', [\App\Http\Controllers\Admin\SponsorshipController::class, 'index'])->name('admin.sponsorships.index');
        Route::get('/sponsorships/create', [\App\Http\Controllers\Admin\SponsorshipController::class, 'create'])->name('admin.sponsorships.create');
        Route::post('/sponsorships', [\App\Http\Controllers\Admin\SponsorshipController::class, 'store'])->name('admin.sponsorships.store');
        Route::get('/sponsorships/{sponsorship}/export', [\App\Http\Controllers\Admin\SponsorshipController::class, 'export'])->name('admin.sponsorships.export');

        // ===== Rute Diskon/Redeem Code =====