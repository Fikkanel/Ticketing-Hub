<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\WaitingRoomEntry;
use App\Models\Event;
use Symfony\Component\HttpFoundation\Response;

class WaitingRoom
{
    /**
     * Cek apakah user boleh masuk ke halaman event, atau harus antri.
     * Middleware ini hanya aktif jika event berstatus 'Upcoming' atau 'Active'
     * DAN kapasitas antrian penuh.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $eventId = $request->route('event_id');

        if (!$eventId) {
            return $next($request);
        }

        // Cek apakah event ada & aktif
        $event = Event::find($eventId);
        if (!$event || !in_array($event->status, ['Upcoming', 'Active'])) {
            // Event tidak ada atau sudah selesai, lewatkan antrian
            return $next($request);
        }

        $sessionId = $request->session()->getId();

        // 1. Bersihkan sesi expired
        WaitingRoomEntry::cleanup($eventId);

        // 2. Promosikan user dari antrian
        WaitingRoomEntry::promoteNext($eventId);

        // 3. Cari/buat entry untuk user ini
        $entry = WaitingRoomEntry::findOrCreateForSession($sessionId, $eventId);

        // 4. Jika belum aktif dan kapasitas penuh → redirect ke waiting room
        if ($entry->status === 'waiting') {
            // Coba promosikan sekali lagi (mungkin ada slot setelah cleanup)
            WaitingRoomEntry::promoteNext($eventId);
            $entry->refresh();

            if ($entry->status === 'waiting') {
                return redirect()->route('public.waiting_room', [
                    'event_id' => $eventId,
                    'token' => $entry->token,
                ]);
            }
        }

        // User aktif — lanjutkan ke halaman event
        return $next($request);
    }
}
