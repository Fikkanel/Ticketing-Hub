<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\Event;
use App\Models\Product;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    /**
     * List bundles for an event
     */
    public function index(Event $event)
    {
        $bundles = $event->bundles()->with('items.product')->get();
        return view('admin.bundles.index', compact('event', 'bundles'));
    }

    /**
     * Show create form
     */
    public function create(Event $event)
    {
        $products = $event->products;
        return view('admin.bundles.form', compact('event', 'products'));
    }

    /**
     * Store new bundle
     */
    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,product_id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $bundle = Bundle::create([
            'event_id' => $event->event_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stok' => $validated['stok'],
            'is_active' => $request->has('is_active'),
        ]);

        // Create bundle items
        foreach ($validated['products'] as $productData) {
            BundleItem::create([
                'bundle_id' => $bundle->id,
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
            ]);
        }

        return redirect()->route('admin.bundles.index', $event)
            ->with('success', 'Bundle berhasil dibuat!');
    }

    /**
     * Show edit form
     */
    public function edit(Event $event, Bundle $bundle)
    {
        $products = $event->products;
        $bundle->load('items.product');
        return view('admin.bundles.form', compact('event', 'bundle', 'products'));
    }

    /**
     * Update bundle
     */
    public function update(Request $request, Event $event, Bundle $bundle)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'is_active' => 'nullable|boolean',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,product_id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        $bundle->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stok' => $validated['stok'],
            'is_active' => $request->has('is_active'),
        ]);

        // Sync bundle items
        $bundle->items()->delete();
        foreach ($validated['products'] as $productData) {
            BundleItem::create([
                'bundle_id' => $bundle->id,
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
            ]);
        }

        return redirect()->route('admin.bundles.index', $event)
            ->with('success', 'Bundle berhasil diperbarui!');
    }

    /**
     * Delete bundle
     */
    public function destroy(Event $event, Bundle $bundle)
    {
        $bundle->delete();
        return redirect()->route('admin.bundles.index', $event)
            ->with('success', 'Bundle berhasil dihapus!');
    }
}
