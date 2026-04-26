<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Scanner Tiket - TixKita</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Bootstrap & CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --scanner-primary: #10b981;
            --scanner-dark: #0f172a;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        html, body { 
            font-family: 'Inter', sans-serif;
            background: var(--scanner-dark);
            color: #fff; 
            height: 100vh;
            height: 100dvh; /* Dynamic viewport height for mobile */
            overflow: hidden;
            position: fixed;
            width: 100%;
        }
        
        .scanner-app {
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
        }
        
        /* Header */
        .scanner-header {
            background: rgba(0,0,0,0.3);
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        
        .scanner-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .scanner-logo i {
            font-size: 1.2rem;
            color: var(--scanner-primary);
        }
        
        .back-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255,255,255,0.1);
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            text-decoration: none;
        }
        
        .back-btn:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }
        
        /* Camera Container - Takes remaining space */
        .camera-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1rem;
            position: relative;
        }
        
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 320px;
            aspect-ratio: 1;
            border-radius: 20px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.3);
        }
        
        #reader {
            width: 100%;
            height: 100%;
        }
        
        #reader video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Scanning Frame */
        .scan-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .scan-frame {
            width: 70%;
            aspect-ratio: 1;
            position: relative;
        }
        
        .scan-frame::before,
        .scan-frame::after,
        .scan-frame .corner-bl,
        .scan-frame .corner-br {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            border-color: var(--scanner-primary);
            border-style: solid;
            border-width: 0;
        }
        
        .scan-frame::before { top: 0; left: 0; border-top-width: 3px; border-left-width: 3px; border-radius: 6px 0 0 0; }
        .scan-frame::after { top: 0; right: 0; border-top-width: 3px; border-right-width: 3px; border-radius: 0 6px 0 0; }
        .scan-frame .corner-bl { bottom: 0; left: 0; border-bottom-width: 3px; border-left-width: 3px; border-radius: 0 0 0 6px; }
        .scan-frame .corner-br { bottom: 0; right: 0; border-bottom-width: 3px; border-right-width: 3px; border-radius: 0 0 6px 0; }
        
        /* Scan Line */
        .scan-line {
            position: absolute;
            left: 10%;
            width: 80%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--scanner-primary), transparent);
            animation: scanLine 2s ease-in-out infinite;
            box-shadow: 0 0 10px var(--scanner-primary);
        }
        
        @keyframes scanLine {
            0%, 100% { top: 15%; opacity: 0.5; }
            50% { top: 80%; opacity: 1; }
        }
        
        /* Status Footer */
        .scanner-footer {
            background: rgba(0,0,0,0.3);
            padding: 1rem;
            text-align: center;
            flex-shrink: 0;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.2);
            border-radius: 30px;
            font-size: 0.85rem;
            color: var(--scanner-primary);
        }
        
        .status-badge i {
            animation: pulse 1.5s infinite;
        }
        
        .status-badge.processing {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
        }
        
        .status-badge.processing i {
            animation: none;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        
        .status-text {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.5);
        }
    </style>
</head>
<body>

<div class="scanner-app">
    {{-- Header --}}
    <header class="scanner-header">
        <div class="scanner-logo">
            <i class="fas fa-qrcode"></i>
            <span>TixKita Scanner</span>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="back-btn" title="Kembali ke Dashboard">
            <i class="fas fa-arrow-left"></i>
        </a>
    </header>

    {{-- Camera --}}
    <div class="camera-wrapper">
        <div class="camera-container">
            <div id="reader"></div>
            <div class="scan-overlay">
                <div class="scan-frame">
                    <div class="corner-bl"></div>
                    <div class="corner-br"></div>
                    <div class="scan-line"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer Status --}}
    <footer class="scanner-footer">
        <div id="status-badge" class="status-badge">
            <i class="fas fa-circle"></i>
            <span id="status-label">Siap Scan</span>
        </div>
        <p id="status-text" class="status-text">Arahkan QR Code tiket ke dalam frame</p>
    </footer>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let html5Qrcode;
    let isProcessing = false;

    function updateStatus(status, label, text) {
        const badge = document.getElementById('status-badge');
        const labelEl = document.getElementById('status-label');
        const textEl = document.getElementById('status-text');
        
        badge.className = 'status-badge ' + status;
        labelEl.textContent = label;
        textEl.textContent = text;
    }

    function onScanSuccess(decodedText) {
        if (isProcessing) return;
        isProcessing = true;

        updateStatus('processing', 'Memproses...', 'Mengecek data tiket');
        html5Qrcode.pause(true);

        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch("{{ route('scanner.process') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken
            },
            body: JSON.stringify({ qr_code: decodedText })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'VALID ✓',
                    html: `
                        <div style="text-align:center;">
                            <p style="font-size:1.2rem;margin:0 0 5px;"><strong>${data.data.customer_name}</strong></p>
                            <p style="color:#666;margin:0;">${data.data.product_name}</p>
                            <p style="color:#999;font-size:0.8rem;margin:5px 0 15px;">${data.data.order_id}</p>
                            <div style="background:#d1fae5;color:#065f46;padding:10px;border-radius:8px;font-weight:600;">
                                SILAKAN MASUK
                            </div>
                        </div>
                    `,
                    timer: 2500,
                    showConfirmButton: false
                }).then(() => resumeScan());
            } else if (data.status === 'warning') {
                Swal.fire({
                    icon: 'warning',
                    title: 'SUDAH DIGUNAKAN',
                    html: `
                        <div style="text-align:center;">
                            <div style="background:#fef3c7;color:#92400e;padding:10px;border-radius:8px;margin-bottom:10px;">
                                Tiket sudah di-scan!
                            </div>
                            <p style="color:#666;font-size:0.85rem;margin:0;">
                                Oleh: ${data.data.scanned_by}<br>
                                ${data.data.scanned_at}
                            </p>
                        </div>
                    `,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#f59e0b'
                }).then(() => resumeScan());
            } else {
                throw new Error(data.message || 'Tiket Tidak Valid');
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'TIDAK VALID',
                text: error.message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#ef4444'
            }).then(() => resumeScan());
        });
    }

    function resumeScan() {
        isProcessing = false;
        updateStatus('', 'Siap Scan', 'Arahkan QR Code tiket ke dalam frame');
        html5Qrcode.resume();
    }

    // Initialize with back camera directly
    html5Qrcode = new Html5Qrcode("reader");
    
    // Get back camera and start
    Html5Qrcode.getCameras().then(devices => {
        if (devices && devices.length) {
            // Find back camera or use first available
            let backCamera = devices.find(d => 
                d.label.toLowerCase().includes('back') || 
                d.label.toLowerCase().includes('belakang') ||
                d.label.toLowerCase().includes('rear') ||
                d.label.toLowerCase().includes('environment')
            );
            
            let cameraId = backCamera ? backCamera.id : devices[devices.length - 1].id;
            
            html5Qrcode.start(
                cameraId,
                {
                    fps: 10,
                    qrbox: { width: 200, height: 200 },
                    aspectRatio: 1.0
                },
                onScanSuccess,
                () => {} // Ignore errors
            ).catch(err => {
                console.error("Camera error:", err);
                updateStatus('processing', 'Error', 'Gagal mengakses kamera');
            });
        }
    }).catch(err => {
        console.error("Camera list error:", err);
        updateStatus('processing', 'Error', 'Tidak dapat mengakses kamera');
    });
</script>
</body>
</html>
