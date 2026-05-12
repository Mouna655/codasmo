<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // ── Filament login menggunakan halaman login kita sendiri ──
            // Jika user langsung akses /admin tanpa login,
            // Filament akan redirect ke /login (bukan /admin/login)
            ->loginRouteSlug('login')
            ->authGuard('web')

            ->colors(['primary' => Color::hex('#1B2A8A')])
            ->brandName('ITM Dashboard')
            ->favicon(asset('img/ITM_Logo_3.png'))

            // ── Session lifetime ──
            // Auto-logout setelah 2 jam tidak aktif
            ->authMiddleware([
                Authenticate::class,
            ])

            ->resources([
                \App\Filament\Resources\ExcelImportResource::class,
                \App\Filament\Resources\PoaImportResource::class,
                \App\Filament\Resources\LoadingImportResource::class,
                \App\Filament\Resources\ShipmentImportResource::class,
                \App\Filament\Resources\ThirdPartyImportResource::class,
                ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ]);
    }
}