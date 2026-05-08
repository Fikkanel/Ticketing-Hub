<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Event; // Diperlukan untuk dropdown event
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar semua produk.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // 1. Ambil daftar Event yang BISA diakses user
        if ($user->isSuperAdmin()) {
            $events = Event::orderBy('tgl_mulai', 'desc')->pluck('judul', 'event_id');
        } else {
            $events = $user->events->pluck('judul', 'event_id');
        }

        // 2. Logika Filtering
        $query = Product::orderBy('created_at', 'desc');

        if ($request->filled('event_id')) {
            // Filter Spesifik jika dipilih
            $query->where('event_id', $request->event_id);
            $selectedEventId = $request->event_id;
        } elseif (!$user->isSuperAdmin()) {
            // Default Admin: Tampilkan produk dari SEMUA event milik dia
            $myEventIds = $user->events->pluck('event_id');
            $query->whereIn('event_id', $myEventIds);
            $selectedEventId = null; // Menandakan "All My Events" atau context global user
        } else {
            // Superadmin tanpa filter: Tampilkan semua (atau kosongkan jika ingin memaksa filter)
            // Sesuai request user "hanya products pada event tersebut", asumsi superadmin masih butuh filter manual.
            // Tapi untuk admin biasa, "sembunyikan filter" dan "tampilkan produk event tersebut".
            $selectedEventId = null;
        }

        $products = $query->get();

        if ($request->ajax()) {
            return response()->json($products);
        }

        return view('admin.products.index', compact('products', 'events', 'selectedEventId'));
    }

    /**
     * Menampilkan formulir untuk membuat produk baru.
     */
    public function create()
    {
        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            // Superadmin: Semua event + opsi null
            $events = Event::pluck('judul', 'event_id')->prepend('Tidak Terikat Event', ''); 
        } else {
            // Admin Biasa: Hanya event yang dikelola
            $events = $user->events->pluck('judul', 'event_id');
            // Opsional: Jika admin harus selalu mengaitkan produk ke event, hapus prepend ini.
            // Namun jika admin boleh buat produk umum (tanpa event), biarkan. 
            // Asumsi: Admin Event hanya mengelola produk eventnya.
            // $events->prepend('Tidak Terikat Event', ''); 
        }
        
        return view('admin.products.create', compact('events'));
    }

    /**
     * Menyimpan produk baru ke database.
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_produk' => 'required|max:150',
            'event_id' => 'nullable|exists:events,event_id',
            'deskripsi' => 'nullable',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'tipe' => 'required|in:Fisik,Digital,Seminar',
            'kategori_tiket' => 'nullable|in:standing,seating',
            'valid_date' => 'nullable|date',
        ];

        // Security Validation for Admin
        if (!auth()->user()->isSuperAdmin() && $request->filled('event_id')) {
            // Pastikan event_id yang dikirim adalah milik admin ini
            if (!auth()->user()->events->contains('event_id', $request->event_id)) {
                 abort(403, 'Anda tidak memiliki akses untuk menambahkan produk ke event ini.');
            }
        }

        $validatedData = $request->validate($rules);

        // Pastikan event tipe seminar tidak menggunakan fitur kursi (seating/standing)
        if ($validatedData['tipe'] === 'Seminar') {
            $validatedData['kategori_tiket'] = null;
        }
        
        $validatedData['is_sponsorship'] = $request->has('is_sponsorship') ? 1 : 0;
        $validatedData['is_hidden'] = $request->has('is_hidden') ? 1 : 0;

        Product::create($validatedData);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Produk baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir edit produk.
     */
    public function edit(Product $product)
    {
        // Check access if product belongs to an event
        if ($product->event_id && !auth()->user()->isSuperAdmin()) {
             if (!auth()->user()->events->contains('event_id', $product->event_id)) {
                abort(403, 'Anda tidak memiliki akses ke produk ini.');
             }
        }

        $user = auth()->user();
        if ($user->isSuperAdmin()) {
            $events = Event::pluck('judul', 'event_id')->prepend('Tidak Terikat Event', '');
        } else {
             $events = $user->events->pluck('judul', 'event_id');
        }

        return view('admin.products.edit', compact('product', 'events'));
    }

    /**
     * Memperbarui produk di database.
     */
    public function update(Request $request, Product $product)
    {
        // Security Check Update
        if ($product->event_id && !auth()->user()->isSuperAdmin()) {
             if (!auth()->user()->events->contains('event_id', $product->event_id)) {
                abort(403, 'Anda tidak memiliki akses ke produk ini.');
             }
        }

        $rules = [
            'nama_produk' => 'required|max:150',
            'event_id' => 'nullable|exists:events,event_id',
            'deskripsi' => 'nullable',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'tipe' => 'required|in:Fisik,Digital,Seminar',
            'kategori_tiket' => 'nullable|in:standing,seating',
            'valid_date' => 'nullable|date',
        ];

        // Security Validation for Input Event ID
        if (!auth()->user()->isSuperAdmin() && $request->filled('event_id')) {
            if (!auth()->user()->events->contains('event_id', $request->event_id)) {
                 abort(403, 'Anda tidak memiliki akses untuk memindahkan produk ke event ini.');
            }
        }

        $validatedData = $request->validate($rules);

        // Pastikan event tipe seminar tidak menggunakan fitur kursi (seating/standing)
        if ($validatedData['tipe'] === 'Seminar') {
            $validatedData['kategori_tiket'] = null;
        }
        
        $validatedData['is_sponsorship'] = $request->has('is_sponsorship') ? 1 : 0;
        $validatedData['is_hidden'] = $request->has('is_hidden') ? 1 : 0;

        $product->update($validatedData);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Produk "' . $product->nama_produk . '" berhasil diperbarui.');
    }

    /**
     * Menghapus produk dari database (Soft Delete).
     */
    public function destroy(Product $product)
    {
        // Karena menggunakan SoftDeletes, metode delete() hanya akan
        // mengisi kolom deleted_at, tidak menghapus baris fisik database.
        // Jadi AMAN dilakukan meskipun produk sudah ada di riwayat transaksi (Order Items).
        
        $nama_produk = $product->nama_produk;

        // Security Check
        if ($product->event_id && !auth()->user()->isSuperAdmin()) {
             if (!auth()->user()->events->contains('event_id', $product->event_id)) {
                abort(403, 'Anda tidak memiliki akses untuk menghapus produk ini.');
             }
        }

        $product->delete(); 
        
        return redirect()->route('admin.products.index')
                         ->with('success', 'Produk "' . $nama_produk . '" berhasil dihapus (diarsipkan).');
    }
}
