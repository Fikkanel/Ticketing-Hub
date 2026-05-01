<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class SponsorshipController extends Controller
{
    /**
     * Tampilkan tiket berdasarkan secret token.
     */
    public function showTicket($token)
    {
        $ticket = Ticket::where('secret_token', $token)
            ->with(['orderItem.product.event.location', 'orderItem.order.customer'])
            ->firstOrFail();

        return view('sponsorship.ticket', compact('ticket'));
    }
}
