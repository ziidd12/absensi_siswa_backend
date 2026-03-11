<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserWebController extends Controller
{
    /**
     * Menampilkan halaman manajemen user
     */
    public function index()
    {
        $data = User::orderBy('created_at', 'desc')->get();
        return view('admin.users.index', compact('data'));
    }

    /**
     * Menyimpan user baru dari web
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|unique:users,email',
            'password'  => 'required|min:6',
            'role'      => ['required', Rule::in(['admin', 'guru', 'siswa'])],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User baru berhasil dibuat!');
    }

    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update user dari web
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role'     => ['required', Rule::in(['admin', 'guru', 'siswa'])],
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        $user->role  = $validated['role'];

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diperbarui!');
    }

    /**
     * Hapus user dari web
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Proteksi agar tidak menghapus diri sendiri
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus!');
    }

    /**
     * Fitur tambahan: Reset Device ID (Penting untuk Siswa)
     */
    public function resetDevice($id)
    {
        $user = User::findOrFail($id);
        $user->update(['device_id' => null]);

        return redirect()->back()->with('success', 'Device ID berhasil di-reset!');
    }
}