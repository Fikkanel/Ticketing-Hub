@extends('layouts.public')

@section('title', 'Syarat & Ketentuan')

@section('content')
<style>
    .terms-section {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }
    .terms-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .terms-title-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background-color: var(--primary-color);
        color: white;
        border-radius: 50%;
        font-weight: 700;
        margin-right: 12px;
        font-size: 0.9rem;
    }
    .terms-heading {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
        font-size: 1.25rem;
        font-weight: 700;
        color: #2c3e50;
    }
    .terms-content-text {
        color: #556b2f; /* Darker tone for better readability */
        color: #4a5568;
        line-height: 1.7;
        margin-left: 44px;
    }
    .terms-content-text ul {
        padding-left: 20px;
    }
    .terms-content-text li {
        margin-bottom: 8px;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="text-center mb-5">
                <h1 class="fw-bold mb-3 text-dark" style="letter-spacing: -0.5px;">Syarat & Ketentuan</h1>
                <p class="text-muted">Terakhir diperbarui: April 2026</p>
            </div>
            
            <div class="alert alert-warning border-0 border-start border-4 border-warning shadow-sm mb-5 bg-white d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-circle fa-2x text-warning me-3"></i>
                <div>
                    <strong class="d-block mb-1">Pemberitahuan Penting</strong>
                    <span class="text-muted small">Harap baca syarat dan ketentuan ini dengan saksama sebelum menggunakan layanan kami. Dengan menggunakan TixKita, Anda menyetujui seluruh ketentuan di bawah ini.</span>
                </div>
            </div>

            {{-- 1. KETENTUAN PENGGUNAAN --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">1</span>
                    Ketentuan Penggunaan
                </div>
                <div class="terms-content-text">
                    <p class="mb-0"><strong>TixKita</strong> ditawarkan kepada Anda, pengguna, dengan syarat Anda menerima syarat, ketentuan, dan pemberitahuan yang terkandung atau tergabung dalam referensi di sini.</p>
                </div>
            </div>

            {{-- 2. RINGKASAN --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">2</span>
                    Ringkasan
                </div>
                <div class="terms-content-text">
                    <p class="mb-0">Penggunaan Situs ini merupakan persetujuan Anda terhadap semua syarat, ketentuan, dan pemberitahuan. Jika Anda tidak setuju, Anda harus segera keluar dari Situs dan menghentikan penggunaan informasi atau produk apa pun dari Situs ini.</p>
                </div>
            </div>

            {{-- 3. MODIFIKASI SITUS --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">3</span>
                    Modifikasi Situs dan Syarat & Ketentuan
                </div>
                <div class="terms-content-text">
                    <p class="mb-0"><strong>TixKita</strong> berhak untuk mengubah, memodifikasi, memperbarui, atau menghentikan syarat, ketentuan, konten, informasi, dan harga kapan saja tanpa pemberitahuan sebelumnya. Kami berhak menyesuaikan harga dari waktu ke waktu. Jika terjadi kesalahan harga, kami berhak menolak pesanan tersebut.</p>
                </div>
            </div>

            {{-- 4. HAK CIPTA --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">4</span>
                    Hak Cipta
                </div>
                <div class="terms-content-text">
                    <p class="mb-0">Kecuali ditentukan lain, semua materi di Situs ini, merek dagang, merek layanan, dan logo adalah milik <strong>TixKita</strong> dan dilindungi oleh undang-undang hak cipta Indonesia dan internasional. Materi tidak boleh disalin atau didistribusikan tanpa izin tertulis sebelumnya.</p>
                </div>
            </div>

            {{-- 5. LISENSI --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">5</span>
                    Pemberian Lisensi & Akses
                </div>
                <div class="terms-content-text">
                    <p class="mb-0">TixKita memberi Anda hak untuk mengakses dan menggunakan Platform semata-mata untuk tujuan pembelian tiket event dan produk digital. Anda tidak boleh melakukan dekompilasi, membongkar (disassemble), atau merekayasa balik (reverse engineer) komponen apa pun dari platform tersebut.</p>
                </div>
            </div>

            {{-- 6. PENDAFTARAN --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">6</span>
                    Pendaftaran (Sign Up)
                </div>
                <div class="terms-content-text">
                    <p class="mb-0">Anda perlu mendaftar di Situs ini untuk melakukan pembelian dengan memasukkan nama, email, dan kata sandi yang valid. Anda bertanggung jawab penuh atas kerahasiaan informasi akun Anda serta segala aktivitas yang terjadi di bawah akun Anda.</p>
                </div>
            </div>

            {{-- 7. DESKRIPSI PRODUK --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">7</span>
                    Deskripsi Produk & Event
                </div>
                <div class="terms-content-text">
                    <p class="mb-0">Kami berusaha sebaik mungkin untuk menampilkan informasi event dan produk seakurat mungkin. Namun, kami tidak menjamin bahwa deskripsi produk, waktu event, atau konten lain di layanan kami benar-benar akurat, lengkap, andal, terkini, atau bebas kesalahan.</p>
                </div>
            </div>

            {{-- 8. REFUND --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">8</span>
                    Ketentuan Pengembalian (Refund)
                </div>
                <div class="terms-content-text">
                    <ul class="mb-0">
                        <li>Tiket event yang sudah dibeli <strong>tidak dapat dikembalikan</strong>, kecuali jika event dibatalkan oleh penyelenggara.</li>
                        <li>Barang fisik harus dikembalikan dalam waktu 7 hari sejak diterima jika terdapat cacat produksi.</li>
                        <li>Barang diskon (sale) tidak memenuhi syarat untuk pengembalian.</li>
                        <li>Keputusan pengembalian dana sepenuhnya merupakan hak prerogatif TixKita.</li>
                    </ul>
                </div>
            </div>

            {{-- 9. PRIVASI --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">9</span>
                    Keamanan & Kebijakan Privasi
                </div>
                <div class="terms-content-text">
                    <p class="mb-0">Informasi Anda aman bersama kami. Kami hanya menggunakan informasi pribadi Anda untuk menyelesaikan pesanan Anda dan tidak akan menyalahgunakan atau menjualnya kepada pihak lain. TixKita akan mengambil semua langkah yang wajar untuk mencegah pelanggaran keamanan pada interaksi server dengan Anda.</p>
                </div>
            </div>

            {{-- 10. GANTI RUGI --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">10</span>
                    Ganti Rugi (Indemnity)
                </div>
                <div class="terms-content-text">
                    <p class="mb-0">Anda setuju untuk mengganti rugi dan membebaskan <strong>TixKita</strong> dari segala klaim pihak ketiga, kerugian, atau biaya (termasuk biaya pengacara) yang timbul dari akses atau penggunaan Anda terhadap Situs ini.</p>
                </div>
            </div>

            {{-- 11. HUKUM --}}
            <div class="terms-section">
                <div class="terms-heading">
                    <span class="terms-title-badge">11</span>
                    Hukum yang Berlaku
                </div>
                <div class="terms-content-text">
                    <p class="mb-0">Syarat dan Ketentuan ini diatur oleh hukum yang berlaku di Indonesia.</p>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
