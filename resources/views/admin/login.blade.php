<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TixKita</title>
    
    {{-- Prevent Browser Cache (Important for login/logout) --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @php
        $primaryColor = $globalSettings['primary_color'] ?? '#3a7d44';
        $secondaryColor = $globalSettings['secondary_color'] ?? '#2e6636';
        $logoPath = $globalSettings['logo_path'] ?? null;
    @endphp

    <style>
        :root {
            --primary: {{ $primaryColor }};
            --secondary: {{ $secondaryColor }};
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(160deg, var(--primary) 0%, var(--secondary) 100%);
            position: relative;
            overflow: hidden;
        }

        /* Subtle pattern overlay */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 85%, rgba(255,255,255,0.07) 0%, transparent 50%),
                radial-gradient(circle at 85% 15%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(0,0,0,0.05) 0%, transparent 60%);
            pointer-events: none;
        }

        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Left Panel */
        .brand-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: #fff;
            position: relative;
        }

        .brand-logo {
            width: 110px;
            height: 110px;
            background: rgba(255,255,255,0.15);
            border-radius: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.2);
            overflow: hidden;
        }
        .brand-logo img {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
        .brand-logo i {
            font-size: 48px;
            color: #fff;
        }

        .brand-panel h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            text-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .brand-panel > p {
            font-size: 1.05rem;
            opacity: 0.85;
            max-width: 320px;
            text-align: center;
            line-height: 1.7;
        }

        .brand-features {
            margin-top: 48px;
            text-align: left;
        }
        .brand-features .feature {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            opacity: 0.9;
        }
        .brand-features .feature .feat-icon {
            width: 42px;
            height: 42px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            font-size: 15px;
            flex-shrink: 0;
        }
        .brand-features .feature span {
            font-size: 0.9rem;
        }

        .version-badge {
            position: absolute;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255,255,255,0.12);
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 0.75rem;
            opacity: 0.7;
        }

        /* Right Panel */
        .form-panel {
            width: 460px;
            min-height: 100vh;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px;
            position: relative;
        }
        .form-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 5px;
            width: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        /* Mobile header banner (hidden on desktop) */
        .mobile-header {
            display: none;
            background: linear-gradient(160deg, var(--primary), var(--secondary));
            padding: 32px 24px 36px;
            text-align: center;
            color: #fff;
            position: relative;
        }
        .mobile-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: #fff;
            border-radius: 24px 24px 0 0;
        }
        .mobile-header img {
            max-height: 44px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .mobile-header .fallback-icon {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        .mobile-header h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .mobile-header p {
            font-size: 0.82rem;
            opacity: 0.85;
            margin: 0;
        }

        .form-header { margin-bottom: 36px; }
        .form-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 6px;
        }
        .form-header p {
            color: #6c757d;
            font-size: 0.92rem;
        }

        .form-group { margin-bottom: 22px; }
        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #344767;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-group .input-wrapper {
            position: relative;
        }
        .form-group .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 15px;
            transition: color 0.3s;
        }
        .form-group input {
            width: 100%;
            padding: 15px 16px 15px 48px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(58, 125, 68, 0.12);
        }
        .form-group .input-wrapper:focus-within i {
            color: var(--primary);
        }
        .form-group input::placeholder {
            color: #adb5bd;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(58, 125, 68, 0.3);
        }
        .btn-login:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(58, 125, 68, 0.4);
        }
        .btn-login:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            margin-top: 28px;
        }
        .back-link a {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
        }
        .back-link a:hover {
            color: var(--primary);
        }
        .back-link a i {
            margin-right: 6px;
        }

        /* Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            animation: slideIn 0.3s ease;
            font-size: 0.9rem;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
        .alert i { margin-right: 10px; font-size: 16px; }
        .alert-close {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            opacity: 0.5;
            transition: opacity 0.3s;
        }
        .alert-close:hover { opacity: 1; }

        /* Responsive */
        @media (max-width: 992px) {
            body { background: #fff; }
            body::before { display: none; }
            .brand-panel { display: none; }
            .login-container { flex-direction: column; }
            .mobile-header { display: block; }
            .form-panel {
                width: 100%;
                min-height: auto;
                padding: 8px 24px 32px;
                justify-content: flex-start;
            }
            .form-panel::before { display: none; }
            .form-header { margin-bottom: 24px; }
        }
        @media (max-width: 576px) {
            .form-panel { padding: 8px 20px 28px; }
            .form-header h2 { font-size: 1.3rem; }
            .mobile-header { padding: 28px 20px 32px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Panel - Branding -->
        <div class="brand-panel">
            <div class="brand-logo">
                @if($logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="TixKita Logo">
                @else
                    <i class="fas fa-ticket-alt"></i>
                @endif
            </div>
            <h1>TixKita</h1>
            <p>Panel administrasi untuk mengelola event, tiket, dan transaksi TixKita.</p>
            
            <div class="brand-features">
                <div class="feature">
                    <div class="feat-icon"><i class="fas fa-chart-line"></i></div>
                    <span>Dashboard Analitik Real-time</span>
                </div>
                <div class="feature">
                    <div class="feat-icon"><i class="fas fa-calendar-check"></i></div>
                    <span>Manajemen Event & Produk</span>
                </div>
                <div class="feature">
                    <div class="feat-icon"><i class="fas fa-users-cog"></i></div>
                    <span>Kontrol Akses Multi-Admin</span>
                </div>
            </div>
            
            <div class="version-badge">TixKita Admin v2.0</div>
        </div>
        
        <!-- Mobile Header Banner (visible on mobile only) -->
        <div class="mobile-header">
            @if($logoPath)
                <img src="{{ asset('storage/' . $logoPath) }}" alt="TixKita">
            @else
                <i class="fas fa-ticket-alt fallback-icon"></i>
            @endif
            <h3>Admin Panel</h3>
            <p>Kelola event & tiket TixKita</p>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="form-panel">
            <div class="form-header">
                <h2><i class="fas fa-shield-alt me-2"></i>Admin Login</h2>
                <p>Masukkan kredensial untuk mengakses dashboard admin.</p>
            </div>
            
            {{-- Alert Messages --}}
            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <span>{{ $error }}</span><br>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" placeholder="admin@tixkita.id" 
                               value="{{ old('email') }}" required autofocus>
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk ke Dashboard
                </button>
            </form>
            
            <div class="back-link">
                <a href="{{ route('public.index') }}">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    </div>
    
    {{-- Force Reload on Back Button (Bypass bfcache) --}}
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>