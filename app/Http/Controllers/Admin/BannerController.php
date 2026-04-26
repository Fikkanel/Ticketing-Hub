<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
                abort(403, 'Akses Ditolak. Hanya Super Admin yang diizinkan.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::orderBy('order')->orderByDesc('created_at')->get();
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
            'image_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB
            'title' => 'nullable|string|max:255',
            'url'   => 'nullable|url',
            'order' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'url', 'order', 'is_active']);
        
        // Upload Image Desktop
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners/desktop', 'public');
            $data['image_path'] = $imagePath;
        }

        // Upload Image Mobile
        $mobileImagePath = null;
        if ($request->hasFile('image_mobile')) {
            $mobileImagePath = $request->file('image_mobile')->store('banners/mobile', 'public');
            $data['mobile_image_path'] = $mobileImagePath;
        }

        if(is_null($data['order'])) $data['order'] = 0;
        $data['is_active'] = $request->has('is_active');

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'image_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'title' => 'nullable|string|max:255',
            'url'   => 'nullable|url',
            'order' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'url', 'order']);
        
        // Update Desktop Image
        if ($request->hasFile('image')) {
            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $data['image_path'] = $request->file('image')->store('banners/desktop', 'public');
        }

        // Update Mobile Image
        if ($request->hasFile('image_mobile')) {
            if ($banner->mobile_image_path && Storage::disk('public')->exists($banner->mobile_image_path)) {
                Storage::disk('public')->delete($banner->mobile_image_path);
            }
            $pathMobile = $request->file('image_mobile')->store('banners/mobile', 'public');
            $data['mobile_image_path'] = $pathMobile;
        }

        $data['is_active'] = $request->has('is_active');
        if(is_null($data['order'])) $data['order'] = 0;

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
            Storage::disk('public')->delete($banner->image_path);
        }
        if ($banner->mobile_image_path && Storage::disk('public')->exists($banner->mobile_image_path)) {
            Storage::disk('public')->delete($banner->mobile_image_path);
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }
}
