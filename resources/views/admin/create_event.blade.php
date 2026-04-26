@extends('layouts.admin')

@section('title', 'Tambah Event Baru')

@section('content')
    <div class="card shadow-sm mb-3">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 fw-bold text-primary">Tambah Event</h6>
            <a href="{{ route('admin.events') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
    <ul class="nav nav-pills nav-fill mb-4 bg-white rounded shadow-sm p-2" id="eventTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="detail-tab" data-bs-toggle="pill" data-bs-target="#tab-detail" type="button" role="tab">
                <i class="fas fa-info-circle me-1"></i> Detail Event & Gambar
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="settings-tab" data-bs-toggle="pill" data-bs-target="#tab-settings" type="button" role="tab">
                <i class="fas fa-cog me-1"></i> Pengaturan
            </button>
        </li>
    </ul>

    <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="tab-content" id="eventTabsContent">
            {{-- ============================================================ --}}
            {{-- TAB 1: DETAIL EVENT & GAMBAR --}}
            {{-- ============================================================ --}}
            <div class="tab-pane fade show active" id="tab-detail" role="tabpanel">
                <div class="row">
                    {{-- KOLOM KIRI: Detail --}}
                    <div class="col-lg-7">
                        {{-- Card: Detail Acara --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white">
                                <h6 class="m-0 fw-bold text-primary">Detail Acara</h6>
                            </div>
                            <div class="card-body">
                                {{-- Judul --}}
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">JUDUL EVENT</label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" name="judul" value="{{ old('judul') }}" required>
                                    @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- Lokasi & Status --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold text-muted">LOKASI</label>
                                        <select class="form-select @error('location_id') is-invalid @enderror" name="location_id" required>
                                            <option value="">-- Pilih Lokasi --</option>
                                            @foreach ($locations as $id => $nama)
                                                <option value="{{ $id }}" {{ old('location_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                                            @endforeach
                                        </select>
                                        @error('location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold text-muted">STATUS</label>
                                        <select class="form-select @error('status') is-invalid @enderror" name="status">
                                            <option value="Upcoming" {{ old('status') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                            <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Finished" {{ old('status') == 'Finished' ? 'selected' : '' }}>Finished</option>
                                        </select>
                                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>



                                {{-- Kategori --}}
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">KATEGORI EVENT <span class="text-muted fw-normal">(Pilih satu atau lebih)</span></label>
                                    <div class="card p-3 bg-light">
                                        <div class="row">
                                            @foreach ($categories as $category)
                                                @if($category->name !== 'Free')
                                                <div class="col-6 col-md-4 col-lg-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="categories[]" value="{{ $category->id }}" 
                                                               id="cat_{{ $category->id }}"
                                                               {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="cat_{{ $category->id }}">
                                                            <i class="fas {{ $category->icon ?? 'fa-tag' }} text-primary me-1"></i> {{ $category->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        <small class="text-muted mt-2"><i class="fas fa-info-circle me-1"></i> Kategori "Free" otomatis ditambahkan jika semua tiket gratis</small>
                                    </div>
                                </div>

                                {{-- Tanggal --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold text-muted">TANGGAL MULAI</label>
                                        <input type="datetime-local" class="form-control @error('tgl_mulai') is-invalid @enderror" name="tgl_mulai" value="{{ old('tgl_mulai') }}" required>
                                        @error('tgl_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small fw-bold text-muted">TANGGAL SELESAI</label>
                                        <input type="datetime-local" class="form-control @error('tgl_selesai') is-invalid @enderror" name="tgl_selesai" value="{{ old('tgl_selesai') }}">
                                        @error('tgl_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                {{-- Deskripsi --}}
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">DESKRIPSI</label>
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" name="deskripsi" rows="4">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- Card: E-Tiket & Email --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white border-left-success" style="border-left: 4px solid #1cc88a;">
                                <h6 class="m-0 fw-bold text-success">Kustomisasi E-Tiket & Email</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">NAMA TIKET (Label)</label>
                                    <input type="text" class="form-control" name="ticket_type" placeholder="Contoh: TIKET KONSER, TIKET WEBINAR" value="{{ old('ticket_type', 'E-TICKET') }}">
                                    <small class="text-muted">Label yang muncul di atas nama event pada tiket & scanner.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">KONTEN CUSTOM EMAIL</label>
                                    <textarea class="form-control" name="custom_email_content" rows="4" placeholder="Tulis pesan tambahan untuk email konfirmasi...">{{ old('custom_email_content') }}</textarea>
                                    <small class="d-block mt-1 text-muted">
                                        <strong>Placeholder tersedia:</strong><br>
                                        <code>{customer_name}</code> : Nama Pemesan<br>
                                        <code>{order_id}</code> : ID Pesanan<br>
                                        <code>{event_name}</code> : Judul Event
                                    </small>
                                </div>
                                
                                <hr class="border-dashed">
                                
                                {{-- Seminar Mode: WhatsApp Link --}}
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">
                                        <i class="fab fa-whatsapp text-success me-1"></i> LINK WHATSAPP SEMINAR (Opsional)
                                    </label>
                                    <input type="url" class="form-control" name="seminar_whatsapp_link" 
                                           placeholder="https://chat.whatsapp.com/ABC123... atau https://wa.me/62812..." 
                                           value="{{ old('seminar_whatsapp_link') }}">
                                    <div class="alert alert-info small mt-2 mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <strong>Mode Seminar:</strong> Jika diisi, event ini akan diperlakukan sebagai seminar:
                                        <ul class="mb-0 mt-1">
                                            <li>Email konfirmasi <strong>tidak</strong> menyertakan lampiran tiket PDF</li>
                                            <li>Halaman invoice menampilkan tombol <strong>"Gabung Grup WhatsApp"</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- KOLOM KANAN: Gambar --}}
                    <div class="col-lg-5">
                        {{-- Card: Banner Image --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white border-left-primary" style="border-left: 4px solid var(--admin-primary);">
                                <h6 class="m-0 fw-bold text-primary">Banner Event (Besar)</h6>
                                <small class="text-muted">Untuk halaman detail & slider (Rek: 1320x300px)</small>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs mb-3" id="bannerTab" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active small" data-bs-toggle="tab" data-bs-target="#banner-upload" type="button">Upload File</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link small" data-bs-toggle="tab" data-bs-target="#banner-link" type="button">Pakai URL</button>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="banner-upload">
                                        <input type="file" class="form-control @error('banner_file') is-invalid @enderror" name="banner_file" accept="image/*">
                                        @error('banner_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="tab-pane fade" id="banner-link">
                                        <input type="url" class="form-control @error('banner_url') is-invalid @enderror" name="banner_url" placeholder="https://example.com/image.jpg">
                                        @error('banner_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Card Image --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white border-left-info" style="border-left: 4px solid #36b9cc;">
                                <h6 class="m-0 fw-bold text-info">Card Image (Kecil)</h6>
                                <small class="text-muted">Untuk daftar event depan (Rek: 600x400px)</small>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs mb-3" id="cardTab" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active small" data-bs-toggle="tab" data-bs-target="#card-upload" type="button">Upload File</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link small" data-bs-toggle="tab" data-bs-target="#card-link" type="button">Pakai URL</button>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="card-upload">
                                        <input type="file" class="form-control @error('card_file') is-invalid @enderror" name="card_file" accept="image/*">
                                        @error('card_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="tab-pane fade" id="card-link">
                                        <input type="url" class="form-control @error('card_url') is-invalid @enderror" name="card_url" placeholder="https://example.com/image.jpg">
                                        @error('card_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- END TAB 1 --}}

            {{-- ============================================================ --}}
            {{-- TAB 2: PENGATURAN --}}
            {{-- ============================================================ --}}
            <div class="tab-pane fade" id="tab-settings" role="tabpanel">
                <div class="row">
                    <div class="col-lg-6">
                        {{-- Card: Pengaturan Pembayaran (Superadmin Only) --}}
                        @if(Auth::user()->isSuperAdmin())
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white border-left-warning" style="border-left: 4px solid #f6c23e;">
                                <h6 class="m-0 fw-bold text-warning">Pengaturan Pembayaran</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="payment_channels" class="form-label fw-bold">Saluran Pembayaran</label>
                                    <select class="form-select @error('payment_channels') is-invalid @enderror" id="payment_channels" name="payment_channels" required>
                                        <option value="all" {{ old('payment_channels') == 'all' ? 'selected' : '' }}>Semua Channel (VA, QRIS, E-Wallet, CC)</option>
                                        <option value="qris_only" {{ old('payment_channels') == 'qris_only' ? 'selected' : '' }}>QRIS Only (Hemat Biaya)</option>
                                    </select>
                                    @error('payment_channels')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="payment_mode" class="form-label fw-bold">Jenis Event (Pajak)</label>
                                    <select class="form-select @error('payment_mode') is-invalid @enderror" id="payment_mode" name="payment_mode" required>
                                        <option value="regular" {{ old('payment_mode') == 'regular' ? 'selected' : '' }}>Regular (Kena Pajak & Admin Fee)</option>
                                        <option value="sponsorship" {{ old('payment_mode') == 'sponsorship' ? 'selected' : '' }}>Sponsorship (Bebas Pajak)</option>
                                    </select>
                                    @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        @else
                        <input type="hidden" name="payment_channels" value="all">
                        <input type="hidden" name="payment_mode" value="regular">
                        @endif

                        {{-- Card: Pengaturan Pembelian --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white border-left-warning" style="border-left: 4px solid #f6c23e;">
                                <h6 class="m-0 fw-bold text-warning">Pengaturan Pembelian</h6>
                                <small class="text-muted">Atur batasan pembelian tiket</small>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted">MAKS. TIKET PER TRANSAKSI</label>
                                    <select class="form-select" name="max_tickets_per_transaction">
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ old('max_tickets_per_transaction', 10) == $i ? 'selected' : '' }}>{{ $i }} Tiket</option>
                                        @endfor
                                    </select>
                                    <small class="text-muted">Jumlah maksimal tiket yang dapat dibeli dalam 1 transaksi</small>
                                </div>
                                <hr class="border-dashed">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="limit_one_email" name="limit_one_email_per_transaction" value="1"
                                           {{ old('limit_one_email_per_transaction') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="limit_one_email">
                                        <strong>1 Email = 1 Transaksi</strong>
                                        <small class="text-muted d-block">Satu akun email hanya dapat melakukan 1 kali transaksi</small>
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="require_unique_data" name="require_unique_data_per_ticket" value="1"
                                           {{ old('require_unique_data_per_ticket') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_unique_data">
                                        <strong>1 Tiket = 1 Data Pemesan</strong>
                                        <small class="text-muted d-block">Setiap tiket memerlukan data pemesan unik (NIK/HP tidak boleh sama)</small>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        {{-- Card: Formulir Data Pemesan --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white border-left-info" style="border-left: 4px solid #36b9cc;">
                                <h6 class="m-0 fw-bold text-info">Formulir Data Pemesan</h6>
                                <small class="text-muted">Pilih data yang wajib diisi pembeli</small>
                            </div>
                            <div class="card-body">
                                @php
                                    $defaultFields = [
                                        ['field' => 'name', 'label' => 'Nama Lengkap', 'enabled' => true, 'required' => true],
                                        ['field' => 'email', 'label' => 'Email', 'enabled' => true, 'required' => true],
                                        ['field' => 'phone', 'label' => 'Nomor HP', 'enabled' => true, 'required' => true],
                                        ['field' => 'nik', 'label' => 'Nomor Identitas (NIK)', 'enabled' => false, 'required' => false],
                                        ['field' => 'dob', 'label' => 'Tanggal Lahir', 'enabled' => false, 'required' => false],
                                        ['field' => 'gender', 'label' => 'Jenis Kelamin', 'enabled' => false, 'required' => false],
                                    ];
                                    $buyerFields = old('buyer_form_fields', $defaultFields);
                                    if (is_string($buyerFields)) $buyerFields = json_decode($buyerFields, true) ?? $defaultFields;
                                @endphp
                                
                                @foreach($defaultFields as $index => $field)
                                    @php
                                        $savedField = collect($buyerFields)->firstWhere('field', $field['field']);
                                        $isEnabled = $savedField['enabled'] ?? $field['enabled'];
                                        $isRequired = $savedField['required'] ?? $field['required'];
                                    @endphp
                                    <div class="d-flex align-items-center justify-content-between mb-2 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="buyer_form_fields[{{ $index }}][enabled]" value="1" 
                                                   id="field_{{ $field['field'] }}"
                                                   {{ $isEnabled ? 'checked' : '' }}>
                                            <input type="hidden" name="buyer_form_fields[{{ $index }}][field]" value="{{ $field['field'] }}">
                                            <label class="form-check-label" for="field_{{ $field['field'] }}">{{ $field['label'] }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="buyer_form_fields[{{ $index }}][required]" value="1"
                                                   id="req_{{ $field['field'] }}"
                                                   {{ $isRequired ? 'checked' : '' }}>
                                            <label class="form-check-label small text-danger" for="req_{{ $field['field'] }}">Wajib</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Card: Custom Form Fields --}}
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 bg-white border-left-success" style="border-left: 4px solid #1cc88a;">
                                <h6 class="m-0 fw-bold text-success">Form Tambahan (Custom)</h6>
                                <small class="text-muted">Tambahkan field custom sesuai kebutuhan event</small>
                            </div>
                            <div class="card-body">
                                <div id="custom-fields-container">
                                    {{-- Custom fields will be added here dynamically --}}
                                </div>
                                <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="addCustomField()">
                                    <i class="fas fa-plus me-1"></i> Tambah Field Custom
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- END TAB 2 --}}
        </div>
        {{-- END TAB CONTENT --}}

        {{-- SUBMIT BUTTON --}}
        <div class="bg-white shadow-lg p-3 rounded mt-4">
            <button type="submit" class="btn btn-success w-100 fw-bold py-3">
                <i class="fas fa-save me-2"></i> SIMPAN EVENT
            </button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
let customFieldIndex = 0;

function addCustomField() {
    const container = document.getElementById('custom-fields-container');
    const html = `
        <div class="custom-field-row border rounded p-3 mb-2 bg-light">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <input type="text" class="form-control form-control-sm" 
                           name="custom_form_fields[${customFieldIndex}][label]" 
                           placeholder="Label Field (cth: Ukuran Baju)" required>
                </div>
                <div class="col-6 col-md-2">
                    <select class="form-select form-select-sm" name="custom_form_fields[${customFieldIndex}][type]">
                        <option value="text">Text</option>
                        <option value="textarea">Textarea</option>
                        <option value="select">Dropdown</option>
                        <option value="checkbox">Checkbox</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <input type="text" class="form-control form-control-sm" 
                           name="custom_form_fields[${customFieldIndex}][options]" 
                           placeholder="Opsi (pisah koma)">
                </div>
                <div class="col-auto">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="custom_form_fields[${customFieldIndex}][required]" value="1">
                        <label class="form-check-label small">Wajib</label>
                    </div>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.custom-field-row').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    customFieldIndex++;
}
</script>
@endsection