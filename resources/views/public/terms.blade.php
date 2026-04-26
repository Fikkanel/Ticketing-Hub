@extends('layouts.public')

@section('title', 'Syarat & Ketentuan')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <h1 class="fw-bold text-center mb-4 text-primary-custom">SYARAT & KETENTUAN</h1>
                
                <div class="alert alert-light border-start border-4 border-warning shadow-sm mb-4">
                    <strong>Penting:</strong> Harap baca syarat dan ketentuan ini dengan saksama sebelum menggunakan layanan kami.
                </div>

                <div class="terms-content">
                    
                    {{-- 1. KETENTUAN PENGGUNAAN --}}
                    <h3 class="fw-bold mt-4 mb-3">1. KETENTUAN PENGGUNAAN</h3>
                    <p>
                        <strong>TixKita</strong> ditawarkan kepada Anda, pengguna, dengan syarat Anda menerima syarat, ketentuan, dan pemberitahuan yang terkandung atau tergabung dalam referensi di sini.
                    </p>

                    {{-- 2. RINGKASAN --}}
                    <h3 class="fw-bold mt-4 mb-3">2. RINGKASAN</h3>
                    <p>
                        Penggunaan Situs ini merupakan persetujuan Anda terhadap semua syarat, ketentuan, dan pemberitahuan. Jika Anda tidak setuju, Anda harus segera keluar dari Situs dan menghentikan penggunaan informasi atau produk apa pun dari Situs ini.
                    </p>

                    {{-- 3. MODIFIKASI SITUS --}}
                    <h3 class="fw-bold mt-4 mb-3">3. MODIFIKASI SITUS DAN SYARAT & KETENTUAN</h3>
                    <p>
                        <strong>TixKita</strong> berhak untuk mengubah, memodifikasi, memperbarui, atau menghentikan syarat, ketentuan, konten, informasi, dan harga kapan saja tanpa pemberitahuan sebelumnya. Kami berhak menyesuaikan harga dari waktu ke waktu. Jika terjadi kesalahan harga, kami berhak menolak pesanan tersebut.
                    </p>

                    {{-- 4. HAK CIPTA --}}
                    <h3 class="fw-bold mt-4 mb-3">4. HAK CIPTA</h3>
                    <p>
                        Kecuali ditentukan lain, semua materi di Situs ini, merek dagang, merek layanan, dan logo adalah milik <strong>TixKita</strong> dan dilindungi oleh undang-undang hak cipta Indonesia dan internasional. Materi tidak boleh disalin atau didistribusikan tanpa izin tertulis sebelumnya.
                    </p>

                    {{-- 5. LISENSI (Barang Digital/Jasa) --}}
                    <h3 class="fw-bold mt-4 mb-3">5. PEMBERIAN LISENSI & AKSES</h3>
                    <p>
                        TixKita memberi Anda hak untuk mengakses dan menggunakan Platform semata-mata untuk tujuan pembelian tiket event dan produk digital. Anda tidak boleh melakukan dekompilasi, membongkar (disassemble), atau merekayasa balik (reverse engineer) komponen apa pun dari platform tersebut.
                    </p>

                    {{-- 6. PENDAFTARAN --}}
                    <h3 class="fw-bold mt-4 mb-3">6. PENDAFTARAN (SIGN UP)</h3>
                    <p>
                        Anda perlu mendaftar di Situs ini untuk melakukan pembelian dengan memasukkan nama, email, dan kata sandi yang valid. Anda bertanggung jawab penuh atas kerahasiaan informasi akun Anda serta segala aktivitas yang terjadi di bawah akun Anda.
                    </p>

                    {{-- 7. DESKRIPSI PRODUK --}}
                    <h3 class="fw-bold mt-4 mb-3">7. DESKRIPSI PRODUK & EVENT</h3>
                    <p>
                        Kami berusaha sebaik mungkin untuk menampilkan informasi event dan produk seakurat mungkin. Namun, kami tidak menjamin bahwa deskripsi produk, waktu event, atau konten lain di layanan kami benar-benar akurat, lengkap, andal, terkini, atau bebas kesalahan.
                    </p>

                    {{-- 8. KETENTUAN PENGEMBALIAN (REFUND) --}}
                    <h3 class="fw-bold mt-4 mb-3">8. KETENTUAN PENGEMBALIAN (REFUND)</h3>
                    <ul>
                        <li>Tiket event yang sudah dibeli <strong>tidak dapat dikembalikan</strong>, kecuali jika event dibatalkan oleh penyelenggara.</li>
                        <li>Barang fisik harus dikembalikan dalam waktu 7 hari sejak diterima jika terdapat cacat produksi.</li>
                        <li>Barang diskon (sale) tidak memenuhi syarat untuk pengembalian.</li>
                        <li>Keputusan pengembalian dana sepenuhnya merupakan hak prerogatif TixKita.</li>
                    </ul>

                    {{-- 9. KEAMANAN & PRIVASI --}}
                    <h3 class="fw-bold mt-4 mb-3">9. KEAMANAN & KEBIJAKAN PRIVASI</h3>
                    <p>
                        Informasi Anda aman bersama kami. Kami hanya menggunakan informasi pribadi Anda untuk menyelesaikan pesanan Anda dan tidak akan menyalahgunakan atau menjualnya kepada pihak lain. TixKita akan mengambil semua langkah yang wajar untuk mencegah pelanggaran keamanan pada interaksi server dengan Anda.
                    </p>

                    {{-- 10. GANTI RUGI --}}
                    <h3 class="fw-bold mt-4 mb-3">10. GANTI RUGI (INDEMNITY)</h3>
                    <p>
                        Anda setuju untuk mengganti rugi dan membebaskan <strong>TixKita</strong> dari segala klaim pihak ketiga, kerugian, atau biaya (termasuk biaya pengacara) yang timbul dari akses atau penggunaan Anda terhadap Situs ini.
                    </p>

                    {{-- 11. HUKUM YANG BERLAKU --}}
                    <h3 class="fw-bold mt-4 mb-3">11. HUKUM YANG BERLAKU</h3>
                    <p>
                        Syarat dan Ketentuan ini diatur oleh hukum yang berlaku di Indonesia.
                    </p>

                </div>
        </div>
    </div>
</div>
@endsection
