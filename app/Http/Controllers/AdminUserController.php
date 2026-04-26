<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Allow Admin & Superadmin
        // Users list: Show Admins (for SA), Scanners (for SA & Admin)
        
        $query = User::query();

        if (auth()->user()->isSuperAdmin()) {
            // Superadmin sees everyone except themselves (optional) or all
            $query->whereIn('role', ['admin', 'superadmin', 'scanner']);
        } else {
            // Regular Admin can only see Scanners
            $query->where('role', 'scanner');
        }

        $users = $query->orderBy('role', 'desc')
                       ->orderBy('name', 'asc')
                       ->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Admin can create scanner
        // Filter events based on logged-in admin's assigned events
        if (auth()->user()->isSuperAdmin()) {
            $events = \App\Models\Event::all();
        } else {
            $assignedEventIds = auth()->user()->events->pluck('event_id');
            $events = \App\Models\Event::whereIn('event_id', $assignedEventIds)->get();
        }
        return view('admin.users.create', compact('events'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $allowedRoles = 'scanner';
        if (auth()->user()->isSuperAdmin()) {
            $allowedRoles = 'admin,superadmin,scanner';
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:' . $allowedRoles,
            'event_ids' => 'nullable|array', 
            'event_ids.*' => 'exists:events,event_id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        if (in_array($request->role, ['admin', 'scanner']) && $request->has('event_ids')) {
            $user->events()->sync($request->event_ids);
        }

        return redirect()->route('admin.users.index')->with('success', 'User baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Prevent Admin from editing Superadmin or other Admins
        if (!auth()->user()->isSuperAdmin() && $user->role !== 'scanner') {
            abort(403);
        }

        // Filter events based on logged-in admin's assigned events
        if (auth()->user()->isSuperAdmin()) {
            $events = \App\Models\Event::all();
        } else {
            $assignedEventIds = auth()->user()->events->pluck('event_id');
            $events = \App\Models\Event::whereIn('event_id', $assignedEventIds)->get();
        }
        return view('admin.users.edit', compact('user', 'events'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Prevent Admin from updating Superadmin or other Admins
        if (!auth()->user()->isSuperAdmin() && $user->role !== 'scanner') {
            abort(403);
        }

        $allowedRoles = 'scanner';
        if (auth()->user()->isSuperAdmin()) {
            $allowedRoles = 'admin,superadmin,scanner';
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed', 
            'role' => 'required|in:' . $allowedRoles,
            'event_ids' => 'nullable|array',
            'event_ids.*' => 'exists:events,event_id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if (in_array($request->role, ['admin', 'scanner'])) {
            $user->events()->sync($request->event_ids ?? []);
        } else {
            $user->events()->detach();
        }

        return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Permission Check:
        // 1. Superadmin can delete anyone (except self, handled below)
        // 2. Admin can ONLY delete Scanner
        if (!auth()->user()->isSuperAdmin() && $user->role !== 'scanner') {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus user ini.');
        }

        // Cegah menghapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Admin berhasil dihapus.');
    }

    /**
     * Toggle status aktif/non-aktif.
     */
    public function toggleStatus(User $user)
    {
        // Permission Check:
        // 1. Superadmin can toggle anyone
        // 2. Admin can ONLY toggle Scanner
        if (!auth()->user()->isSuperAdmin() && $user->role !== 'scanner') {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah status user ini.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }
}
