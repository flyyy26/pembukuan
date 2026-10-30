<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /**
     * Attach user login ke karyawan (dipanggil dari halaman karyawan).
     */
    public function attachToKaryawan(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'karyawan_id' => $karyawan->id,
                'name'        => $karyawan->nama,
                'email'       => $request->email,
                'password'    => Hash::make($request->password),
                'can_login'   => true,
                'must_change_password' => true,
            ]);

            $user->assignRole('karyawan');

            if ($request->permissions) {
                $user->syncPermissions($request->permissions);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Akun login untuk '{$karyawan->nama}' berhasil dibuat.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update permission karyawan.
     */
    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Jangan izinkan edit permission admin
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Permission admin tidak bisa diubah.'], 422);
        }

        $user->syncPermissions($request->permissions ?? []);

        return response()->json(['success' => true, 'message' => 'Permission berhasil diperbarui.']);
    }

    /**
     * Reset password user oleh admin.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Password berhasil direset.']);
    }

    /**
     * Matikan / aktifkan login user.
     */
    public function toggleLogin(User $user)
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Akun admin tidak bisa dinonaktifkan loginnya.'], 422);
        }

        $user->update(['can_login' => !$user->can_login]);

        return response()->json([
            'success' => true,
            'message' => $user->can_login ? 'Login diaktifkan.' : 'Login dinonaktifkan.',
            'can_login' => $user->can_login,
        ]);
    }

    /**
     * Hapus akun login (karyawan tetap ada).
     */
    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Akun admin tidak bisa dihapus.'], 422);
        }

        $user->delete();

        return response()->json(['success' => true, 'message' => 'Akun login berhasil dihapus.']);
    }

    /**
     * Ambil daftar semua permission untuk UI.
     */
    public function listPermissions()
    {
        return Permission::orderBy('name')->pluck('name');
    }
}