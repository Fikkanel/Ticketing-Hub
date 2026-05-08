<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscountController extends Controller
{
    /**
     * Menampilkan daftar diskon dan form generate/create.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            // Superadmin melihat semua diskon
            $discounts = Discount::with('event')->orderBy('created_at', 'desc')->get();
            // Superadmin bisa pilih semua event untuk dropdown
            $events = \App\Models\Event::orderBy('judul', 'asc')->get();
        } else {
            // Admin biasa hanya melihat diskon yang berelasi dengan event yang dia kelola
            // Asumsi: Admin TIDAK melihat diskon global (event_id = NULL)
            $eventIds = $user->events->pluck('event_id');
            
            $discounts = Discount::with('event')
                ->whereIn('event_id', $eventIds)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Admin hanya bisa pilih event miliknya
            $events = $user->events;
        }

        return view('admin.discounts.index', compact('discounts', 'events'));
    }

    /**
     * Menyimpan diskon baru dari form manual.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $rules = [
            'code' => 'required|string|unique:discounts,code|max:50',
            'percentage' => 'required|integer|min:1|max:100',
            'max_uses' => 'required|integer|min:1',
            'expires_at' => 'nullable|date|after:today',
        ];

        // Validasi event_id
        if ($user->isSuperAdmin()) {
            $rules['event_id'] = 'nullable|exists:events,event_id';
        } else {
            // Admin WAJIB pilih event dan HARUS event miliknya
            $rules['event_id'] = [
                'required', 
                'exists:events,event_id',
                function ($attribute, $value, $fail) use ($user) {
                    if (!$user->events->contains('event_id', $value)) {
                        $fail('Anda tidak memiliki akses ke event ini.');
                    }
                },
            ];
        }

        $validatedData = $request->validate($rules);

        Discount::create($validatedData);

        return redirect()->route('admin.discounts.index')
                         ->with('success', 'Kode Diskon berhasil dibuat.');
    }

    /**
     * Menghasilkan kode diskon acak dan menyimpannya.
     */
    public function generate(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'generate_percentage' => 'required|integer|min:1|max:100',
            'generate_max_uses' => 'required|integer|min:1',
        ];

        // Validasi event_id (sama dengan store)
        if ($user->isSuperAdmin()) {
            $rules['event_id'] = 'nullable|exists:events,event_id';
        } else {
            $rules['event_id'] = [
                'required', 
                'exists:events,event_id',
                function ($attribute, $value, $fail) use ($user) {
                    if (!$user->events->contains('event_id', $value)) {
                        $fail('Anda tidak memiliki akses ke event ini.');
                    }
                },
            ];
        }

        $request->validate($rules);

        // Generate kode unik 8 karakter
        do {
            $code = Str::upper(Str::random(8));
        } while (Discount::where('code', $code)->exists());

        Discount::create([
            'code' => $code,
            'percentage' => $request->generate_percentage,
            'max_uses' => $request->generate_max_uses,
            'is_active' => true,
            'event_id' => $request->event_id, // Simpan event_id
        ]);

        return redirect()->route('admin.discounts.index')
                         ->with('success', "Kode Diskon Otomatis **{$code}** (Diskon {$request->generate_percentage}%) berhasil dibuat.");
    }

    /**
     * Menghapus kode diskon.
     */
    public function destroy(Discount $discount)
    {
        // Cek otorisasi hapus
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            // Admin hanya boleh hapus jika diskon ini milik event dia
            if (!$discount->event_id || !$user->events->contains('event_id', $discount->event_id)) {
                return redirect()->route('admin.discounts.index')
                                 ->with('error', 'Anda tidak berhak menghapus diskon ini.');
            }
        }

        $discount->delete();
        return redirect()->route('admin.discounts.index')
                         ->with('success', 'Kode Diskon berhasil dihapus.');
    }
}
