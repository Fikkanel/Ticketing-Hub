<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Menampilkan daftar semua lokasi.
     */
    public function index()
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        $locations = Location::all();
        return view('admin.locations.index', compact('locations'));
    }

    /**
     * Menampilkan formulir untuk membuat lokasi baru.
     */
    public function create()
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        return view('admin.locations.create');
    }

    /**
     * Menyimpan lokasi baru ke database.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);

        $validatedData = $request->validate([
            'nama_lokasi' => 'required|max:150',
            'alamat' => 'required',
            'kota' => 'nullable|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'map_link' => 'nullable|string',
        ]);

        Location::create($validatedData);

        return redirect()->route('admin.locations.index')
                         ->with('success', 'Lokasi baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir edit lokasi.
     */
    public function edit(Location $location)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);
        return view('admin.locations.edit', compact('location'));
    }

    /**
     * Memperbarui lokasi di database.
     */
    public function update(Request $request, Location $location)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);

        $validatedData = $request->validate([
            'nama_lokasi' => 'required|max:150',
            'alamat' => 'required',
            'kota' => 'nullable|max:50',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'map_link' => 'nullable|string',
        ]);

        $location->update($validatedData);

        return redirect()->route('admin.locations.index')
                         ->with('success', 'Lokasi "' . $location->nama_lokasi . '" berhasil diperbarui.');
    }

    /**
     * Menghapus lokasi dari database.
     */
    public function destroy(Location $location)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);

        try {
            $nama_lokasi = $location->nama_lokasi;
            $location->delete();
            
            return redirect()->route('admin.locations.index')
                             ->with('success', 'Lokasi "' . $nama_lokasi . '" berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Error ini akan muncul jika Lokasi masih digunakan oleh tabel Events
            return redirect()->route('admin.locations.index')
                             ->with('error', 'Gagal menghapus lokasi karena masih terikat dengan Event yang ada.');
        }
    }
}