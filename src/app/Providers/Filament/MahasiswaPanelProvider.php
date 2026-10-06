<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
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
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class MahasiswaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('mahasiswa')
            ->path('mahasiswa')
            ->spa()
            ->brandName('SIFAK Mahasiswa')
            ->login()
            ->defaultThemeMode(ThemeMode::Light)
            ->font('Montserrat')
            ->colors([
                'primary' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\AksesRolePage::class,
            ])
            ->resources([
                \App\Filament\Admin\Resources\MahasiswaResource::class,
                \App\Filament\Admin\Resources\KrsResource::class,
                \App\Filament\Admin\Resources\KrsDetailResource::class,
                \App\Filament\Admin\Resources\KrsValidationResultResource::class,
                \App\Filament\Admin\Resources\PenawaranMataKuliahResource::class,
                \App\Filament\Admin\Resources\KelasKuliahResource::class,
                \App\Filament\Admin\Resources\JadwalKuliahResource::class,
                \App\Filament\Admin\Resources\JadwalHistoryResource::class,
                \App\Filament\Admin\Resources\PendaftaranSidangResource::class,
                \App\Filament\Admin\Resources\SidangTypeResource::class,
                \App\Filament\Admin\Resources\SidangRequirementResource::class,
                \App\Filament\Admin\Resources\SidangRegistrationResource::class,
                \App\Filament\Admin\Resources\SidangScheduleResource::class,
                \App\Filament\Admin\Resources\SidangResultResource::class,
                \App\Filament\Admin\Resources\SidangRevisionResource::class,
                \App\Filament\Admin\Resources\SidangMinuteResource::class,
                \App\Filament\Admin\Resources\SuratResource::class,
                \App\Filament\Admin\Resources\AlertResource::class,
                \App\Filament\Admin\Resources\DokumenTaResource::class,
                \App\Filament\Admin\Resources\TugasAkhirResource::class,
                \App\Filament\Admin\Resources\TaSectionResource::class,
                \App\Filament\Admin\Resources\TaDocumentResource::class,
                \App\Filament\Admin\Resources\TaDocumentVersionResource::class,
                \App\Filament\Admin\Resources\TaCommentResource::class,
                \App\Filament\Admin\Resources\TaProgressLogResource::class,
                \App\Filament\Admin\Resources\RepositoryItemResource::class,
                \App\Filament\Admin\Resources\RevisionCycleResource::class,
                \App\Filament\Admin\Resources\DosenResource::class,
                \App\Filament\Admin\Resources\MataKuliahResource::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                \App\Http\Middleware\ResolveTenantBySubdomain::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
