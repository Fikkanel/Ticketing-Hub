<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - TixKita</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%); padding: 30px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">🔐 Reset Password</h1>
        </div>
        
        <!-- Content -->
        <div style="padding: 30px;">
            <p style="font-size: 16px; color: #333; margin-bottom: 20px;">
                Halo <strong>{{ $customerName }}</strong>,
            </p>
            
            <p style="font-size: 14px; color: #666; line-height: 1.6;">
                Kami menerima permintaan untuk mereset password akun TixKita Anda. Klik tombol di bawah ini untuk membuat password baru:
            </p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" 
                   style="display: inline-block; background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%); color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 8px; font-weight: bold; font-size: 16px;">
                    Reset Password
                </a>
            </div>
            
            <p style="font-size: 13px; color: #999; line-height: 1.6;">
                Link ini akan kadaluarsa dalam <strong>60 menit</strong>. Jika Anda tidak meminta reset password, abaikan email ini.
            </p>
            
            <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;">
            
            <p style="font-size: 12px; color: #999; text-align: center;">
                Jika tombol tidak berfungsi, copy dan paste link berikut di browser Anda:<br>
                <a href="{{ $resetUrl }}" style="color: #1cc88a; word-break: break-all;">{{ $resetUrl }}</a>
            </p>
        </div>
        
        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 20px; text-align: center;">
            <p style="margin: 0; color: #666; font-size: 12px;">
                &copy; {{ date('Y') }} TixKita. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
