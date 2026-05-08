<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerAuthController extends Controller
{
    /**
     * Tampilkan halaman login customer.
     */
    public function showLoginForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }
        return view('customer.auth.login');
    }

    /**
     * Tampilkan halaman register customer.
     */
    public function showRegisterForm()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }
        return view('customer.auth.register');
    }

    /**
     * Proses login dengan email dan password.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $email = strtolower(trim($request->email));

        // Coba login menggunakan Auth attempt
        if (Auth::guard('customer')->attempt(['email' => $email, 'password' => $request->password], $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $customer = Auth::guard('customer')->user();
            
            Log::info("Customer login successful: {$email}");

            return redirect()->intended(route('customer.dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $customer->name . '!');
        }

        // Jika gagal
        Log::warning("Customer login failed for {$email}");
        
        // Cek apakah email ada tapi password salah, atau email tidak ada
        $customerExists = Customer::where('email', $email)->exists();

        if ($customerExists) {
             return back()->with('error', 'Password yang Anda masukkan salah.')
                ->withInput($request->only('email'));
        }

        return back()->with('error', 'Email belum terdaftar. Silakan daftar terlebih dahulu.')
            ->withInput($request->only('email'));
    }

    /**
     * Proses pendaftaran langsung (tanpa OTP, dengan Captcha).
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'captcha' => 'required|captcha',
        ], [
            'captcha.captcha' => 'Kode captcha yang Anda masukkan tidak sesuai. Silakan coba lagi.'
        ]);

        $email = strtolower(trim($request->email));

        // Cek apakah email sudah terdaftar
        $existingCustomer = Customer::where('email', $email)->first();

        if ($existingCustomer) {
            return back()->with('error', 'Email sudah terdaftar. Silakan login.')
                ->withInput($request->except('password', 'password_confirmation', 'captcha'));
        }

        // Buat customer baru
        $customer = Customer::create([
            'name' => $request->name,
            'email' => $email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(), // Otomatis verified
        ]);

        // Login customer
        Auth::guard('customer')->login($customer, true);

        return redirect()->route('customer.dashboard')
            ->with('success', 'Selamat datang di Ticketing Hub! Akun Anda berhasil dibuat.');
    }

    /**
     * Logout customer.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Dashboard customer.
     */
    public function dashboard()
    {
        $customer = Auth::guard('customer')->user();
        $orders = $customer->orders()
            ->with(['orderItems.product.event'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.dashboard', compact('customer', 'orders'));
    }

    /**
     * Tampilkan form lupa password.
     */
    public function showForgotPasswordForm()
    {
        return view('customer.auth.forgot-password');
    }

    /**
     * Kirim link reset password ke email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $customer = Customer::where('email', $email)->first();

        if (!$customer) {
            return back()->with('error', 'Email tidak terdaftar di sistem kami.')
                ->withInput();
        }

        // Generate token
        $token = \Illuminate\Support\Str::random(64);

        // Simpan atau update token di database
        \Illuminate\Support\Facades\DB::table('customer_password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Buat URL reset password
        $resetUrl = route('customer.password.reset', ['token' => $token, 'email' => $email]);

        // Kirim email
        try {
            Mail::to($email)->send(new \App\Mail\CustomerPasswordResetMail($resetUrl, $customer->name));
            
            Log::info("Password reset link sent to {$email}");
            
            return back()->with('success', 'Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.');
        } catch (\Exception $e) {
            Log::error("Failed to send password reset email: " . $e->getMessage());
            return back()->with('error', 'Gagal mengirim email. Silakan coba lagi nanti.')
                ->withInput();
        }
    }

    /**
     * Tampilkan form reset password.
     */
    public function showResetForm(Request $request)
    {
        $token = $request->token;
        $email = $request->email;

        // Validasi token exists
        $resetRecord = \Illuminate\Support\Facades\DB::table('customer_password_resets')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return redirect()->route('customer.password.request')
                ->with('error', 'Link reset password tidak valid atau sudah kadaluarsa.');
        }

        // Cek apakah token sudah kadaluarsa (60 menit)
        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            // Hapus token yang sudah kadaluarsa
            \Illuminate\Support\Facades\DB::table('customer_password_resets')->where('email', $email)->delete();
            
            return redirect()->route('customer.password.request')
                ->with('error', 'Link reset password sudah kadaluarsa. Silakan request lagi.');
        }

        // Verifikasi token
        if (!Hash::check($token, $resetRecord->token)) {
            return redirect()->route('customer.password.request')
                ->with('error', 'Link reset password tidak valid.');
        }

        return view('customer.auth.reset-password', compact('token', 'email'));
    }

    /**
     * Proses reset password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = strtolower(trim($request->email));

        // Validasi token
        $resetRecord = \Illuminate\Support\Facades\DB::table('customer_password_resets')
            ->where('email', $email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return back()->with('error', 'Token reset password tidak valid.');
        }

        // Cek kadaluarsa
        $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            \Illuminate\Support\Facades\DB::table('customer_password_resets')->where('email', $email)->delete();
            return redirect()->route('customer.password.request')
                ->with('error', 'Link reset password sudah kadaluarsa.');
        }

        // Update password customer
        $customer = Customer::where('email', $email)->first();
        if (!$customer) {
            return back()->with('error', 'Customer tidak ditemukan.');
        }

        $customer->password = Hash::make($request->password);
        $customer->save();

        // Hapus token dari database
        \Illuminate\Support\Facades\DB::table('customer_password_resets')->where('email', $email)->delete();

        Log::info("Password reset successful for {$email}");

        return redirect()->route('customer.login')
            ->with('success', 'Password berhasil direset! Silakan login dengan password baru Anda.');
    }

}
