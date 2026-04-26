<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

// app/Http/Middleware/Authenticate.php

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        // Cek jika route yang diakses berawalan 'customer' atau terkait public checkout
        if ($request->is('customer*') || $request->is('checkout*') || $request->is('cart*')) {
            return route('customer.login');
        }

        // Default ke admin login
        return route('admin.login');
    }
}