<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan form login.
     * Jika sudah login sebagai admin/operator → redirect ke dashboard
     * (bukan kembali ke login)
     */
    public function create(): View|RedirectResponse
    {
        // Jika sudah login sebagai admin/operator, tidak perlu login lagi
        // KECUALI mereka datang via /admin-access (session sudah dihapus)
        if (auth()->check()) {
            $role = auth()->user()->role;
            if (in_array($role, ['superadmin', 'operator'])) {
                return redirect()->intended(route('dashboard.daily'));
            }
        }

        return view('auth.login');
    }

    /**
     * Proses login.
     *
     * Hasil:
     * - superadmin/operator → /dashboard/daily
     * - karyawan → TOLAK, tidak boleh login lewat sini
     * - akun tidak aktif → TOLAK
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        // Double-check: karyawan tidak boleh login lewat halaman ini
        if ($user->role === 'karyawan') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Karyawan tidak perlu login. Dashboard dapat diakses langsung di halaman utama.',
                ]);
        }

        // Cek akun aktif
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Akun Anda tidak aktif. Hubungi administrator.',
                ]);
        }

        $request->session()->regenerate();

        // Redirect ke dashboard
        return redirect()->intended(route('dashboard.daily'));
    }

    /**
     * Logout — semua user redirect ke home (bukan ke /login)
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}