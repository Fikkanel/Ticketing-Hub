<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'payment/notification',
        'api/midtrans/callback',  // Midtrans webhook callback
        'api/validate-discount', // Existing API route usually needs exemption too if likely to be called externally, but keeping minimal change.
    ];
}
