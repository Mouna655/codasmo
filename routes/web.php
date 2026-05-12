<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicDashboardController;
use App\Http\Controllers\PoaController;
use App\Http\Controllers\LoadingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ThirdPartyController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// ═══════════════════════════════════════════════════════════════
// PUBLIC ROUTES — tidak perlu login (karyawan & siapapun)
// ═══════════════════════════════════════════════════════════════
Route::get('/',        [PublicDashboardController::class, 'landing'])->name('home');
Route::get('/daily',   [PublicDashboardController::class, 'daily'])->name('public.daily');
Route::get('/weekly',  [PublicDashboardController::class, 'weekly'])->name('public.weekly');
Route::get('/poa',     [PoaController::class, 'index'])->name('public.poa');
Route::get('/loading', [LoadingController::class, 'index'])->name('public.loading');
Route::get('/shipment',[ShipmentController::class, 'index'])->name('public.shipment');
Route::get('/third-party', [ThirdPartyController::class, 'index'])->name('public.third-party');

// API publik
Route::prefix('api')->group(function () {
    Route::get('/dashboard/daily', [PublicDashboardController::class, 'apiDaily']);
    Route::get('/poa/data',        [PoaController::class, 'apiData'])->name('api.poa');
    Route::get('/loading/data',    [LoadingController::class, 'apiData'])->name('api.loading');
    Route::get('/shipment/data',   [ShipmentController::class, 'apiData'])->name('api.shipment');
    Route::get('/api/third-party/data', [ThirdPartyController::class, 'apiData']);

});

// ═══════════════════════════════════════════════════════════════
// KUNCI KEAMANAN #1 — Force Re-Login
//
// Route ini SELALU menghapus session yang ada, kemudian
// redirect ke /login. Dipakai oleh tombol "Admin Panel"
// di landing page dan semua halaman publik.
//
// Tujuan: mencegah shared session — siapapun yang klik
// "Admin Panel" harus buktikan identitasnya sendiri.
// ═══════════════════════════════════════════════════════════════
Route::get('/admin-access', function () {
    // ── Cek siapa yang sedang login ──────────────────────────
    if (auth()->check()) {
        $user = auth()->user();

        // Sudah login sebagai admin/operator yang valid
        // → JANGAN logout, langsung arahkan ke dashboard
        // → Tidak ada efek samping bagi admin yang sedang kerja
        if (in_array($user->role, ['superadmin', 'operator']) && $user->is_active) {
            return redirect()->route('dashboard.daily');
        }

        // Sudah login tapi sebagai karyawan
        // → Logout karyawan ini, minta mereka login ulang sebagai admin
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('admin_required', 'Halaman ini hanya untuk Admin dan Operator.');
    }

    // Belum login sama sekali → arahkan ke login
    return redirect()->route('login')
        ->with('admin_required', 'Silahkan login sebagai Admin atau Operator.');

})->name('admin.access');

// ═══════════════════════════════════════════════════════════════
// LOGIN — hanya untuk guest
// Jika sudah login sebagai admin/operator → redirect ke dashboard
// Jika sudah login sebagai karyawan → redirect ke home
// ═══════════════════════════════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// ═══════════════════════════════════════════════════════════════
// LOGOUT — semua role yang login bisa logout
// ═══════════════════════════════════════════════════════════════
Route::middleware('auth')->post('/logout',
    [AuthenticatedSessionController::class, 'destroy']
)->name('logout');

// ═══════════════════════════════════════════════════════════════
// PROTECTED ROUTES — hanya superadmin & operator
// Middleware 'admin.only' blokir karyawan
// ═══════════════════════════════════════════════════════════════
Route::middleware(['auth', 'admin.only'])->group(function () {
    Route::get('/dashboard', fn() => redirect()->route('dashboard.daily'))->name('dashboard');
    Route::get('/dashboard/daily', [DashboardController::class, 'index'])->name('dashboard.daily');
    Route::get('/dashboard/poa',   [PoaController::class, 'adminIndex'])->name('dashboard.poa');
    Route::get('/dashboard/loading',  [LoadingController::class, 'adminIndex'])->name('dashboard.loading');
    Route::get('/dashboard/shipment', [ShipmentController::class, 'adminIndex'])->name('dashboard.shipment');
    Route::get('/dashboard/third-party', [ThirdPartyController::class, 'dashboard'])->name('dashboard.third-party');

});

// API protected
Route::middleware(['auth', 'admin.only'])->prefix('api')->group(function () {
    Route::get('/dashboard/data', [DashboardController::class, 'apiData'])->name('api.dashboard');
    Route::get('/dashboard/third-party/data', [ThirdPartyController::class, 'apiDashboardData'])->name('api.dashboard.third-party');
    Route::post('/poa/update-provisional', [PoaController::class, 'updateProvisional'])->name('api.poa.update-provisional');

});