<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->firstAllowedUrl(Auth::user()));
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $credentials['can_login'] = true;

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // ✅ BERSIHKAN CACHE PERMISSION SAAT LOGIN
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $user = Auth::user();
            
            // ✅ FORCE LOAD ULANG PERMISSION DARI DATABASE
            // Ini memastikan $user->can() membaca data terbaru
            $user->unsetRelation('roles');
            $user->unsetRelation('permissions');
            $user->load('roles', 'permissions');

            $fallback = $this->firstAllowedUrl($user);
            return redirect()->intended($fallback)
                ->with('success', 'Selamat datang, ' . $user->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Email atau password salah, atau akun tidak aktif.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda berhasil logout.');
    }

    /**
     * Cari URL pertama yang boleh diakses user berdasarkan permission.
     * Admin selalu diarahkan ke /shopee.
     */
    private function firstAllowedUrl($user): string
    {
        // Admin: ke shopee
        if ($user->isAdmin()) {
            return '/shopee';
        }

        // Urutan prioritas halaman untuk karyawan
        $map = [
            'view shopee'         => '/shopee',
            'view tiktok'         => '/tiktok',
            'view tiktok live'    => '/tiktok/live',
            'view web-sales'      => '/reports/web-sales',
            'view brilink'        => '/brilink',
            'view tabungan'       => '/tabungan',
            'view hutang'         => '/hutang',
            'view piutang'        => '/piutang',
            'view zakat'          => '/zakat',
            'view karyawan'       => '/karyawan',
            'view gaji'           => '/gaji',
            'view minus'          => '/minus-karyawan',
            'view konveksi'       => '/konveksi',
            'view import shopee'  => '/import',
            'view import tiktok'  => '/tiktok/import',
            'view import brilink' => '/brilink/import',
            'view products'       => '/products',
            'view stores'         => '/stores',
        ];

        foreach ($map as $perm => $url) {
            if ($user->can($perm)) {
                return $url;
            }
        }

        // Tidak punya akses apapun
        return '/no-access';
    }
}