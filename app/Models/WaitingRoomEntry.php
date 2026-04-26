<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WaitingRoomEntry extends Model
{
    protected $fillable = [
        'session_id', 'event_id', 'token', 'status', 'activated_at', 'last_activity_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    // === KONFIGURASI ===
    const MAX_ACTIVE_USERS = 50;        // Maks user aktif per event
    const ACTIVE_TIMEOUT_MINUTES = 10;  // Timeout sesi aktif (menit)
    const WAITING_CLEANUP_HOURS = 2;    // Hapus antrian lama setelah X jam

    /**
     * Bersihkan sesi aktif yang sudah timeout & antrian lama.
     */
    public static function cleanup(int $eventId): void
    {
        // Expire active sessions yang sudah timeout
        self::where('event_id', $eventId)
            ->where('status', 'active')
            ->where('last_activity_at', '<', now()->subMinutes(self::ACTIVE_TIMEOUT_MINUTES))
            ->update(['status' => 'expired']);

        // Hapus antrian lama (> 2 jam)
        self::where('event_id', $eventId)
            ->where('created_at', '<', now()->subHours(self::WAITING_CLEANUP_HOURS))
            ->delete();
    }

    /**
     * Hitung jumlah user aktif untuk event tertentu.
     */
    public static function activeCount(int $eventId): int
    {
        return self::where('event_id', $eventId)
                   ->where('status', 'active')
                   ->count();
    }

    /**
     * Promosikan user dari antrian ke aktif (FIFO).
     */
    public static function promoteNext(int $eventId): void
    {
        $available = self::MAX_ACTIVE_USERS - self::activeCount($eventId);

        if ($available > 0) {
            $entries = self::where('event_id', $eventId)
                          ->where('status', 'waiting')
                          ->orderBy('created_at', 'asc')
                          ->limit($available)
                          ->get();

            foreach ($entries as $entry) {
                $entry->update([
                    'status' => 'active',
                    'activated_at' => now(),
                    'last_activity_at' => now(),
                ]);
            }
        }
    }

    /**
     * Cari atau buat entry antrian untuk session + event.
     */
    public static function findOrCreateForSession(string $sessionId, int $eventId): self
    {
        // Cek apakah sudah punya entry yang aktif/waiting
        $entry = self::where('session_id', $sessionId)
                     ->where('event_id', $eventId)
                     ->whereIn('status', ['waiting', 'active'])
                     ->first();

        if ($entry) {
            // Refresh activity timestamp jika aktif
            if ($entry->status === 'active') {
                $entry->update(['last_activity_at' => now()]);
            }
            return $entry;
        }

        // Buat entry baru
        return self::create([
            'session_id' => $sessionId,
            'event_id' => $eventId,
            'token' => Str::random(64),
            'status' => 'waiting',
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Hitung posisi antrian user ini.
     */
    public function getPositionAttribute(): int
    {
        if ($this->status === 'active') return 0;

        return self::where('event_id', $this->event_id)
                   ->where('status', 'waiting')
                   ->where('created_at', '<=', $this->created_at)
                   ->count();
    }

    /**
     * Estimasi waktu tunggu (menit).
     */
    public function getEstimatedWaitAttribute(): int
    {
        // Rata-rata 1 user butuh 3 menit, promosi per batch MAX_ACTIVE_USERS
        $position = $this->position;
        return max(1, (int) ceil($position / self::MAX_ACTIVE_USERS) * 3);
    }
}
