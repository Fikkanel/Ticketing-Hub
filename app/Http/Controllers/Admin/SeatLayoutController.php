<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SeatLayout;
use App\Models\Seat;
use Illuminate\Http\Request;

class SeatLayoutController extends Controller
{
    public function index(Product $product)
    {
        // Pastikan hanya bisa diakses untuk tipe seating
        if ($product->kategori_tiket !== 'seating') {
            return redirect()->route('admin.products.index')
                             ->with('error', 'Produk ini bukan bertipe seating.');
        }

        // Security check
        if ($product->event_id && !auth()->user()->isSuperAdmin()) {
             if (!auth()->user()->events->contains('event_id', $product->event_id)) {
                abort(403, 'Anda tidak memiliki akses ke produk ini.');
             }
        }

        $seatLayout = $product->seatLayout()->with('seats')->first();
        
        // Default values jika belum ada layout
        $rows = $seatLayout ? $seatLayout->rows : 10;
        $columns = $seatLayout ? $seatLayout->columns : 10;
        $stagePosition = $seatLayout ? $seatLayout->stage_position : 'top';
        $stageShape = $seatLayout ? $seatLayout->stage_shape : 'normal';
        $seatsData = [];
        
        if ($seatLayout) {
            foreach ($seatLayout->seats as $seat) {
                $seatsData[$seat->row_index][$seat->col_index] = $seat;
            }
        }

        return view('admin.products.seat_layout', compact('product', 'seatLayout', 'rows', 'columns', 'stagePosition', 'stageShape', 'seatsData'));
    }

    public function store(Request $request, Product $product)
    {
        // Security check
        if ($product->event_id && !auth()->user()->isSuperAdmin()) {
             if (!auth()->user()->events->contains('event_id', $product->event_id)) {
                abort(403, 'Anda tidak memiliki akses ke produk ini.');
             }
        }

        $request->validate([
            'rows' => 'required|integer|min:1|max:100',
            'columns' => 'required|integer|min:1|max:100',
            'stage_position' => 'required|in:top,bottom,left,right',
            'stage_shape' => 'required|in:normal,convex,concave',
            'seats' => 'required|array',
        ]);

        $seatLayout = $product->seatLayout()->updateOrCreate(
            ['product_id' => $product->product_id],
            [
                'rows' => $request->rows,
                'columns' => $request->columns,
                'stage_position' => $request->stage_position,
                'stage_shape' => $request->stage_shape,
            ]
        );

        // Delete existing seats
        $seatLayout->seats()->delete();

        $seatsToInsert = [];
        $now = now();
        foreach ($request->seats as $seatData) {
            // $seatData should be json decoded from frontend
            $seatObj = json_decode($seatData, true);
            $seatsToInsert[] = [
                'seat_layout_id' => $seatLayout->id,
                'row_index' => $seatObj['row'],
                'col_index' => $seatObj['col'],
                'seat_number' => $seatObj['label'],
                'is_active' => filter_var($seatObj['active'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert in chunks for performance
        foreach (array_chunk($seatsToInsert, 500) as $chunk) {
            Seat::insert($chunk);
        }

        // Update product stock based on active seats
        $activeSeatsCount = array_reduce($seatsToInsert, function($carry, $item) {
            return $carry + ($item['is_active'] ? 1 : 0);
        }, 0);
        $product->update(['stok' => $activeSeatsCount]);

        return redirect()->route('admin.products.seat_layout', $product->product_id)
                         ->with('success', 'Layout kursi berhasil disimpan. Stok produk otomatis diperbarui menjadi ' . $activeSeatsCount . '.');
    }
}
