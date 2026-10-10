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

class DosenPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dosen')
            ->path('dosen')
            ->spa()
            ->brandName('SIFAK Dosen')
            ->login()
            ->defaultThemeMode(ThemeMode::Light)
            ->font('Montserrat')
            ->colors([
                'primary' => Color::Teal,
                'info' => Color::Sky,
                'warning' => Color::Amber,
            ])
            ->navigation(fn (\Filament\Navigation\NavigationBuilder $builder) => \App\Filament\Support\RoleNavigation::build($builder))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(\Filament\Support\Enums\MaxWidth::SevenExtraLarge)
            ->pages([
                \App\Filament\Pages\SifakDashboard::class,
                \App\Filament\Pages\AksesRolePage::class,
            ])
            ->resources([
                \App\Filament\Admin\Resources\DosenResource::class,
                \App\Filament\Admin\Resources\JadwalKuliahResource::class,
                \App\Filament\Admin\Resources\JadwalHistoryResource::class,
                \App\Filament\Admin\Resources\JadwalConflictResource::class,
                \App\Filament\Admin\Resources\KelasKuliahResource::class,
                \App\Filament\Admin\Resources\PlottingDosenResource::class,
                \App\Filament\Admin\Resources\MahasiswaResource::class,
                \App\Filament\Admin\Resources\KrsResource::class,
                \App\Filament\Admin\Resources\KrsDetailResource::class,
                \App\Filament\Admin\Resources\KrsValidationResultResource::class,
                \App\Filament\Admin\Resources\SuratResource::class,
                \App\Filament\Admin\Resources\GeneratedLetterResource::class,
                \App\Filament\Admin\Resources\LetterDistributionResource::class,
                \App\Filament\Admin\Resources\LetterArchiveResource::class,
                \App\Filament\Admin\Resources\AlertResource::class,
                \App\Filament\Admin\Resources\MonitoringSnapshotResource::class,
                \App\Filament\Admin\Resources\MonitoringIndicatorResultResource::class,
                \App\Filament\Admin\Resources\AlertFollowupResource::class,
                \App\Filament\Admin\Resources\AlertEscalationResource::class,
                \App\Filament\Admin\Resources\MahasiswaProfileResource::class,
                \App\Filament\Admin\Resources\MahasiswaInterestResource::class,
                \App\Filament\Admin\Resources\MahasiswaCertificationResource::class,
                \App\Filament\Admin\Resources\MahasiswaPortfolioResource::class,
                \App\Filament\Admin\Resources\MahasiswaOrganizationResource::class,
                \App\Filament\Admin\Resources\MahasiswaMbkmResource::class,
                \App\Filament\Admin\Resources\MahasiswaCplScoreResource::class,
                \App\Filament\Admin\Resources\MahasiswaPloScoreResource::class,
                \App\Filament\Admin\Resources\MahasiswaGraduateProfileScoreResource::class,
                \App\Filament\Admin\Resources\CompetencyGapResource::class,
                \App\Filament\Admin\Resources\StudentRecommendationResource::class,
                \App\Filament\Admin\Resources\RecommendationHistoryResource::class,
                \App\Filament\Admin\Resources\DokumenTaResource::class,
                \App\Filament\Admin\Resources\TugasAkhirResource::class,
                \App\Filament\Admin\Resources\TaSectionResource::class,
                \App\Filament\Admin\Resources\TaDocumentResource::class,
                \App\Filament\Admin\Resources\TaDocumentVersionResource::class,
                \App\Filament\Admin\Resources\TaReviewResource::class,
                \App\Filament\Admin\Resources\TaCommentResource::class,
                \App\Filament\Admin\Resources\TaApprovalResource::class,
                \App\Filament\Admin\Resources\TaProgressLogResource::class,
                \App\Filament\Admin\Resources\RepositoryItemResource::class,
                \App\Filament\Admin\Resources\RevisionCycleResource::class,
                \App\Filament\Admin\Resources\PendaftaranSidangResource::class,
                \App\Filament\Admin\Resources\SidangTypeResource::class,
                \App\Filament\Admin\Resources\SidangRequirementResource::class,
                \App\Filament\Admin\Resources\SidangRegistrationResource::class,
                \App\Filament\Admin\Resources\SidangAssignmentResource::class,
                \App\Filament\Admin\Resources\SidangScheduleResource::class,
                \App\Filament\Admin\Resources\SidangRubricResource::class,
                \App\Filament\Admin\Resources\SidangScoreResource::class,
                \App\Filament\Admin\Resources\SidangResultResource::class,
                \App\Filament\Admin\Resources\SidangRevisionResource::class,
                \App\Filament\Admin\Resources\SidangMinuteResource::class,
                \App\Filament\Admin\Resources\KeahlianResource::class,
                \App\Filament\Admin\Resources\DosenProfilResource::class,
                \App\Filament\Admin\Resources\DosenPendidikanResource::class,
                \App\Filament\Admin\Resources\DosenSertifikasiResource::class,
                \App\Filament\Admin\Resources\DosenPublikasiResource::class,
                \App\Filament\Admin\Resources\DosenPengalamanIndustriResource::class,
                \App\Filament\Admin\Resources\RiwayatMengajarResource::class,
                \App\Filament\Admin\Resources\DosenPreferensiMkResource::class,
                \App\Filament\Admin\Resources\BebanDosenResource::class,
                \App\Filament\Admin\Resources\MatriksKesesuaianResource::class,
                \App\Filament\Admin\Resources\RekomendasiPengampuResource::class,
                \App\Filament\Admin\Resources\JadwalKonsultasiResource::class,
                \App\Filament\Admin\Resources\DosenLokasiResource::class,
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
            ->authMiddleware([Authenticate::class, \App\Http\Middleware\EnforceRoleFeatures::class]);
    }
}
