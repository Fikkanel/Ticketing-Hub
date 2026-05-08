@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                <h6 class="m-0 fw-bold text-primary">Pengaturan Website</h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="site_title" class="form-label">Judul Situs</label>
                        <input type="text" class="form-control" id="site_title" name="site_title" value="{{ $settings['site_title'] ?? 'Ticketing Hub' }}">
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo Website <small class="text-muted">(Rekomendasi: <strong>500x183 px</strong>)</small></label>
                        @if(isset($settings['logo_path']) && $settings['logo_path'])
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo" style="max-height: 100px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah logo.</small>
                    </div>

                    <div class="mb-3">
                        <label for="favicon" class="form-label">Favicon <small class="text-muted">(Tab Browser)</small></label>
                        @if(isset($settings['favicon_path']) && $settings['favicon_path'])
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['favicon_path']) }}" alt="Favicon" style="max-height: 32px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="favicon" name="favicon" accept="image/*">
                        <small class="text-muted">Upload gambar kecil (e.g. 32x32px) untuk ikon di tab browser.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="primary_color" class="form-label">Warna Utama (Primary)</label>
                            <input type="color" class="form-control form-control-color" id="primary_color" name="primary_color" value="{{ $settings['primary_color'] ?? '#3b82f6' }}" title="Choose your color">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="secondary_color" class="form-label">Warna Sekunder (Secondary)</label>
                            <input type="color" class="form-control form-control-color" id="secondary_color" name="secondary_color" value="{{ $settings['secondary_color'] ?? '#1e3a8a' }}" title="Choose your color">
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3 border-bottom pb-2">Banner Profil Organizer</h5>
                    <div class="mb-3">
                        <label for="organizer_banner" class="form-label">
                            Banner Header Organizer <small class="text-muted">(Rekomendasi: <strong>1400x300 px</strong>)</small>
                        </label>
                        @if(isset($settings['organizer_banner_path']) && $settings['organizer_banner_path'])
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['organizer_banner_path']) }}" alt="Organizer Banner" class="img-fluid rounded" style="max-height: 150px;">
                            </div>
                        @endif
                        <input type="file" class="form-control" id="organizer_banner" name="organizer_banner" accept="image/*">
                        <small class="text-muted">Banner ini akan tampil di semua halaman profil organizer.</small>
                    </div>

                    <h5 class="mt-4 mb-3 border-bottom pb-2">Kustomisasi Header & Footer</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary fw-bold">Header (Navbar)</h6>
                            <div class="mb-3">
                                <label for="header_bg_color" class="form-label small">Background Color</label>
                                <input type="color" class="form-control form-control-color" id="header_bg_color" name="header_bg_color" value="{{ $settings['header_bg_color'] ?? '#ffffff' }}">
                            </div>
                            <div class="mb-3">
                                <label for="header_text_color" class="form-label small">Text Color (Brand & Links)</label>
                                <input type="color" class="form-control form-control-color" id="header_text_color" name="header_text_color" value="{{ $settings['header_text_color'] ?? '#000000' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary fw-bold">Footer</h6>
                            <div class="mb-3">
                                <label for="footer_bg_color" class="form-label small">Background Color</label>
                                <input type="color" class="form-control form-control-color" id="footer_bg_color" name="footer_bg_color" value="{{ $settings['footer_bg_color'] ?? '#ffffff' }}">
                            </div>
                            <div class="mb-3">
                                <label for="footer_text_color" class="form-label small">Text Color</label>
                                <input type="color" class="form-control form-control-color" id="footer_text_color" name="footer_text_color" value="{{ $settings['footer_text_color'] ?? '#333333' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Link Footer - Menu</label>
                            <div class="mb-2">
                                <label for="footer_blog_link" class="small">Blog Link</label>
                                <input type="url" class="form-control" id="footer_blog_link" name="footer_blog_link" value="{{ $settings['footer_blog_link'] ?? '' }}" placeholder="https://...">
                            </div>
                            <div class="mb-2">
                                <label for="footer_career_link" class="small">Karir Link</label>
                                <input type="url" class="form-control" id="footer_career_link" name="footer_career_link" value="{{ $settings['footer_career_link'] ?? '' }}" placeholder="https://...">
                            </div>
                            <div class="mb-2">
                                <label for="footer_contact_link" class="small">Hubungi Kami Link</label>
                                <input type="url" class="form-control" id="footer_contact_link" name="footer_contact_link" value="{{ $settings['footer_contact_link'] ?? '' }}" placeholder="https://...">
                            </div>
                            <div class="mb-2">
                                <label for="footer_faq_link" class="small">FAQ Link</label>
                                <input type="url" class="form-control" id="footer_faq_link" name="footer_faq_link" value="{{ $settings['footer_faq_link'] ?? '' }}" placeholder="https://...">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Link Social Media</label>
                            <div class="mb-2">
                                <label for="social_instagram" class="small"><i class="fab fa-instagram"></i> Instagram</label>
                                <input type="url" class="form-control" id="social_instagram" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/...">
                            </div>
                            <div class="mb-2">
                                <label for="social_twitter" class="small"><i class="fab fa-twitter"></i> Twitter / X</label>
                                <input type="url" class="form-control" id="social_twitter" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}" placeholder="https://twitter.com/...">
                            </div>
                            <div class="mb-2">
                                <label for="social_facebook" class="small"><i class="fab fa-facebook"></i> Facebook</label>
                                <input type="url" class="form-control" id="social_facebook" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/...">
                            </div>
                            <div class="mb-2">
                                <label for="social_linkedin" class="small"><i class="fab fa-linkedin"></i> LinkedIn</label>
                                <input type="url" class="form-control" id="social_linkedin" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}" placeholder="https://linkedin.com/in/...">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
                </form>
            </div>
        </div>
        


        {{-- DANGER ZONE --}}
        <div class="card shadow mb-4 border-left-danger">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 fw-bold"><i class="fas fa-exclamation-triangle"></i> Danger Zone: Hapus Data Transaksi</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <strong>PERHATIAN!</strong> Fitur ini digunakan untuk menghapus data transaksi (Pesanan & Tiket) secara permanen. 
                    Biasanya digunakan setelah masa testing selesai atau ingin mereset data event tertentu.
                    <br>
                    Data yang dicadangkan tidak dapat dikembalikan.
                </div>

                <form action="{{ route('admin.settings.reset_transactions') }}" method="POST" id="resetForm">
                    @csrf
                    @method('DELETE')
                    
                    <div class="row align-items-end">
                        <div class="col-md-5">
                            <label for="event_id" class="form-label fw-bold">Pilih Event untuk Direset</label>
                            <select class="form-select" name="event_id" id="event_id" required>
                                <option value="" disabled selected>-- Pilih Event --</option>
                                <option value="all" class="text-danger fw-bold">!!! SEMUA EVENT (RESET TOTAL) !!!</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->event_id }}">{{ $event->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="password_confirm" class="form-label fw-bold">Konfirmasi Password Admin</label>
                            <input type="password" class="form-control" name="password" id="password_confirm" placeholder="Masukkan password Anda saat ini" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger w-100" onclick="confirmReset()">
                                <i class="fas fa-trash"></i> Hapus Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    function confirmReset() {
        const eventSelect = document.getElementById('event_id');
        const eventName = eventSelect.options[eventSelect.selectedIndex].text;
        
        if (eventSelect.value === "") {
            Swal.fire('Error', 'Silakan pilih event terlebih dahulu.', 'error');
            return;
        }

        Swal.fire({
            title: 'Apakah Anda Yakin?',
            html: `Anda akan menghapus data transaksi untuk: <br><strong>${eventName}</strong>.<br><br>Tindakan ini tidak bisa dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Data!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('resetForm').submit();
            }
        });
    }
</script>
@endsection
