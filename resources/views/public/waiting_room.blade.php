@extends('layouts.public')

@section('title', 'Antrian - ' . $event->judul)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="text-center mb-4">
                <div class="waiting-icon mb-3">
                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="40" cy="40" r="38" stroke="var(--primary-color, #198754)" stroke-width="3" fill="none" opacity="0.2"/>
                        <circle cx="40" cy="40" r="38" stroke="var(--primary-color, #198754)" stroke-width="3" fill="none"
                                stroke-dasharray="239" stroke-dashoffset="60"
                                class="spinner-circle"/>
                    </svg>
                    <div class="waiting-clock">
                        <i class="fas fa-ticket-alt fa-2x" style="color: var(--primary-color, #198754);"></i>
                    </div>
                </div>
                <h4 class="fw-bold text-dark">Ruang Tunggu Virtual</h4>
                <p class="text-muted small mb-0">{{ $event->judul }}</p>
            </div>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Header --}}
                <div class="card-header bg-primary-custom text-white text-center py-3" style="background: linear-gradient(135deg, var(--primary-color, #198754), #0a6e3f) !important;">
                    <i class="fas fa-users me-2"></i>
                    Permintaan tinggi untuk event ini
                </div>
                
                <div class="card-body p-4 text-center">
                    {{-- Posisi Antrian --}}
                    <div class="mb-4">
                        <p class="text-muted mb-1 small text-uppercase fw-bold ls-1">Posisi Antrian Anda</p>
                        <div class="display-3 fw-bold queue-position" style="color: var(--primary-color, #198754);" id="queuePosition">
                            {{ $entry->position }}
                        </div>
                    </div>

                    {{-- Estimasi Waktu --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-3">
                                <div class="small text-muted mb-1"><i class="far fa-clock me-1"></i> Estimasi</div>
                                <div class="fw-bold" id="estimatedWait">~{{ $entry->estimated_wait }} menit</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded-3 p-3">
                                <div class="small text-muted mb-1"><i class="fas fa-users me-1"></i> Di Depan Anda</div>
                                <div class="fw-bold" id="aheadCount">{{ max(0, $entry->position - 1) }} orang</div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-3">
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 id="progressBar"
                                 style="width: 5%;">
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3" id="statusArea">
                        <div class="spinner-grow spinner-grow-sm text-success" role="status"></div>
                        <span class="text-muted small" id="statusText">Halaman akan otomatis terbuka saat giliran Anda...</span>
                    </div>

                    <hr>

                    {{-- Info --}}
                    <div class="small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Jangan tutup halaman ini. Anda akan diarahkan otomatis saat giliran tiba.
                    </div>
                </div>
            </div>

            {{-- Tombol Kembali --}}
            <div class="text-center mt-3">
                <a href="{{ route('public.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar Event
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .waiting-icon {
        position: relative;
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }
    .waiting-clock {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .spinner-circle {
        animation: spin 2s linear infinite;
        transform-origin: center;
    }
    @keyframes spin {
        100% { transform: rotate(360deg); }
    }
    .queue-position {
        font-size: 4rem;
        line-height: 1;
        animation: pulse-number 2s ease-in-out infinite;
    }
    @keyframes pulse-number {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    .ls-1 { letter-spacing: 1px; }

    /* Saat redirect */
    .redirecting .queue-position {
        color: #198754 !important;
        animation: none;
    }
</style>
@endsection

@section('scripts')
<script>
    const TOKEN = '{{ $entry->token }}';
    const EVENT_ID = {{ $event->event_id }};
    let pollInterval = null;
    let pollCount = 0;

    function checkStatus() {
        pollCount++;
        
        fetch(`/waiting-room/status/${EVENT_ID}?token=${TOKEN}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'active') {
                // GILIRAN TIBA! Redirect ke event
                clearInterval(pollInterval);
                document.getElementById('statusArea').innerHTML = `
                    <span class="text-success fw-bold">
                        <i class="fas fa-check-circle me-1"></i> Giliran Anda! Mengalihkan...
                    </span>`;
                document.getElementById('queuePosition').textContent = '✓';
                document.getElementById('queuePosition').style.color = '#198754';
                document.getElementById('progressBar').style.width = '100%';
                
                setTimeout(() => {
                    window.location.href = `/events/${EVENT_ID}`;
                }, 1500);
            } else if (data.status === 'waiting') {
                // Update posisi
                document.getElementById('queuePosition').textContent = data.position;
                document.getElementById('estimatedWait').textContent = `~${data.estimated_wait} menit`;
                document.getElementById('aheadCount').textContent = `${Math.max(0, data.position - 1)} orang`;
                
                // Update progress (inverse of position)
                let progress = Math.max(5, 100 - (data.position * 2));
                document.getElementById('progressBar').style.width = `${progress}%`;
            } else {
                // Expired — reload untuk dapat antrian baru
                window.location.reload();
            }
        })
        .catch(err => {
            console.warn('Poll error:', err);
        });
    }

    // Poll setiap 5 detik
    pollInterval = setInterval(checkStatus, 5000);
    
    // First check after 3 seconds
    setTimeout(checkStatus, 3000);
</script>
@endsection
