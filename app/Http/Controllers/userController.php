<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function findUserById($id)
    {
        return User::where('id', Auth::id())->findOrFail($id);
    }

    /**
     * Get user login (profile)
     */
    public function index(Request $request)
    {
        $data = User::where('id', Auth::id())->first();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create user (biasanya admin / registrasi)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|unique:users,email',
            'password' => 'required|min:6',
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
            'device_id' => 'nullable|string',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'status' => (bool) $user,
            'message' => $user ? 'User berhasil dibuat' : 'User gagal dibuat',
            'data' => $user,
        ], $user ? 201 : 500);
    }

    /**
     * Show user detail
     */
    public function show($id)
    {
        $user = $this->findUserById($id);

        return response()->json([
            'status' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update user login
     */
    public function update(Request $request, $id)
    {
        $user = $this->findUserById($id);

        $validated = $request->validate([
            'email' => [
                'required',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|min:6',
            'role' => ['required', Rule::in(['admin', 'guru', 'siswa'])],
            'device_id' => 'nullable|string',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $status = $user->update($validated);

        return response()->json([
            'status' => (bool) $status,
            'message' => $status ? 'User berhasil diupdate' : 'User gagal diupdate',
            'data' => $user,
        ], $status ? 200 : 500);
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = $this->findUserById($id);

        $status = $user->delete();

        return response()->json([
            'status' => (bool) $status,
            'message' => $status ? 'User berhasil dihapus' : 'User gagal dihapus',
        ], $status ? 200 : 500);
    }
}