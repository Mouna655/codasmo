<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Middleware ini melindungi semua route /dashboard dan /admin.
     *
     * Logika:
     * 1. Tidak login → redirect ke /login
     * 2. Login tapi karyawan → logout paksa + redirect ke /login dengan pesan error
     * 3. Login sebagai superadmin/operator → lanjut
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Belum login sama sekali
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Anda harus login untuk mengakses halaman ini.');
        }

        $user = auth()->user();

        // Akun tidak aktif
        if (!$user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // Login tapi bukan admin/operator (karyawan mencoba masuk)
        if (!in_array($user->role, ['superadmin', 'operator'])) {
            // Paksa logout karyawan yang nyasar
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Halaman ini hanya dapat diakses oleh Admin dan Operator.');
        }

        return $next($request);
    }
}