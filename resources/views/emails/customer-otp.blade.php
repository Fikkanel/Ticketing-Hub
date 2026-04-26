<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'register' ? 'Kode Verifikasi Pendaftaran' : 'Kode Login' }} - TixKita</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #3a7d44 0%, #2e6636 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .otp-code {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 12px;
            color: #3a7d44;
            background-color: #f0f4ff;
            padding: 25px 35px;
            border-radius: 12px;
            display: inline-block;
            margin: 20px 0;
            border: 2px dashed #3a7d44;
        }
        .message {
            color: #666;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            font-size: 13px;
            margin-top: 20px;
        }
        .footer {
            background-color: #f8f9fc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 TixKita</h1>
        </div>
        
        <div class="content">
            <div class="icon">
                {{ $type === 'register' ? '✉️' : '🔐' }}
            </div>
            
            <h2 style="color: #333; margin-bottom: 10px;">
                {{ $type === 'register' ? 'Verifikasi Email Anda' : 'Masuk ke Akun Anda' }}
            </h2>
            
            <p class="message">
                @if($type === 'register')
                    Terima kasih telah mendaftar di TixKita! Gunakan kode OTP di bawah ini untuk memverifikasi email Anda.
                @else
                    Kami menerima permintaan untuk masuk ke akun TixKita Anda. Gunakan kode OTP di bawah ini.
                @endif
            </p>
            
            <div class="otp-code">{{ $otp }}</div>
            
            <p class="message" style="font-size: 13px; color: #888;">
                Kode ini berlaku selama <strong>10 menit</strong>.
            </p>
            
            <div class="warning">
                ⚠️ <strong>Jangan bagikan kode ini kepada siapapun.</strong><br>
                Tim TixKita tidak akan pernah meminta kode OTP Anda.
            </div>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} TixKita. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
