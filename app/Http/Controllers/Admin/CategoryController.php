<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Cek apakah user adalah superadmin
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
                abort(403, 'Akses ditolak. Hanya Superadmin yang dapat mengelola kategori.');
            }
            return $next($request);
        });
    }

    /**
     * Daftar semua kategori
     */
    public function index()
    {
        $categories = Category::withCount('events')->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Form tambah kategori
     */
    public function create()
    {
        $icons = Category::availableIcons();
        return view('admin.categories.form', compact('icons'));
    }

    /**
     * Simpan kategori baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
            'icon' => 'nullable|string|max:50',
            'requires_nik' => 'nullable|boolean',
        ]);
        
        // Checkbox: convert to boolean
        $validated['requires_nik'] = $request->has('requires_nik');

        $validated['slug'] = Str::slug($validated['name']);

        // Pastikan slug unik
        $count = 1;
        $originalSlug = $validated['slug'];
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Form edit kategori
     */
    public function edit(Category $category)
    {
        $icons = Category::availableIcons();
        return view('admin.categories.form', compact('category', 'icons'));
    }

    /**
     * Update kategori
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:categories,name,' . $category->id,
            'icon' => 'nullable|string|max:50',
            'requires_nik' => 'nullable|boolean',
        ]);
        
        // Checkbox: convert to boolean
        $validated['requires_nik'] = $request->has('requires_nik');

        // Update slug jika nama berubah
        if ($category->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Pastikan slug unik
            $count = 1;
            $originalSlug = $validated['slug'];
            while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori
     */
    public function destroy(Category $category)
    {
        // Cek apakah kategori masih digunakan oleh event
        if ($category->events()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Tidak dapat menghapus kategori yang masih digunakan oleh event.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
