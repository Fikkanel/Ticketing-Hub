@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                <h6 class="m-0 fw-bold text-primary">Pengaturan Pajak & Biaya Layanan</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.transaction.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group mb-3">
                                <label class="fw-bold small text-muted">BIAYA ADMIN</label>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="input-group">
                                            <input type="number" step="0.1" class="form-control" name="tax_admin_fee_percentage" value="{{ \App\Models\Setting::get('tax_admin_fee_percentage', 3) }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <small class="text-muted d-block mt-1">Persentase</small>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group">
                                            <span class="input-group-text">Min</span>
                                            <input type="number" class="form-control" name="tax_admin_fee_min" value="{{ \App\Models\Setting::get('tax_admin_fee_min', 2000) }}">
                                        </div>
                                        <small class="text-muted d-block mt-1">Minimal (Rp)</small>
                                    </div>
                                </div>
                                <small class="text-success fw-bold mt-1 d-block">Rumus: Max(Subtotal * %, Minimum)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="fw-bold small text-muted">PPN / VAT (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" class="form-control" name="tax_ppn_percentage" value="{{ \App\Models\Setting::get('tax_ppn_percentage', 11) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Dihitung dari (Biaya Admin + Biaya Layanan).</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-primary fw-bold mb-3">Biaya Layanan (Payment Channel)</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="small fw-bold">Virtual Account (Fixed)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="tax_service_fee_va" value="{{ \App\Models\Setting::get('tax_service_fee_va', 4500) }}">
                                </div>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="small fw-bold">E-Wallet / GoPay (Fixed)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" name="tax_service_fee_gopay" value="{{ \App\Models\Setting::get('tax_service_fee_gopay', 2000) }}">
                                </div>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="small fw-bold">QRIS (Percentage)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" class="form-control" name="tax_service_fee_qris" value="{{ \App\Models\Setting::get('tax_service_fee_qris', 0.7) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Contoh: 0.7 untuk 0.7%</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success fw-bold px-4 mt-3">
                        <i class="fas fa-save me-2"></i> Simpan Pengaturan Biaya
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 fw-bold text-success">Pengaturan Pembayaran</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Semua pembayaran kini diproses otomatis melalui <strong>Midtrans Payment Gateway</strong>.</p>
                <p class="small">Silakan konfigurasi API Key Midtrans Anda melalui file <code>.env</code>.</p>
            </div>
        </div>
    </div>
</div>
@endsection
