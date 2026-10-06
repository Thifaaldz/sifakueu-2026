<?php

namespace App\Filament\Pages;

use App\Support\Access\ActorModuleRegistry;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class AksesRolePage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Jobdesk';

    protected static ?string $navigationLabel = 'Modul & Hak Akses';

    protected static ?int $navigationSort = -1;

    protected static string $view = 'filament.pages.akses-role';

    protected static ?string $slug = 'modul-hak-akses';

    public function getTitle(): string | Htmlable
    {
        return 'Modul & Hak Akses';
    }

    public function getSubheading(): ?string
    {
        return 'Ringkasan menu dan batas akses sesuai dokumen aktor SIFAK.';
    }

    public function getActorModules(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $roles = $user->roles->pluck('name')->all();
        $panelId = Filament::getCurrentPanel()?->getId();

        if ($panelId === 'super-admin') {
            $roles = array_values(array_intersect($roles, ['super_admin']));
        }

        if ($panelId === 'admin') {
            $roles = array_values(array_intersect($roles, ['admin_fakultas', 'admin_prodi']));
        }

        if ($panelId === 'mahasiswa') {
            $roles = array_values(array_intersect($roles, ['mahasiswa']));
        }

        if ($panelId === 'dosen') {
            $roles = array_values(array_intersect($roles, ['dosen', 'dosen_pa', 'dosen_pembimbing', 'dosen_penguji']));
        }

        if ($panelId === 'pimpinan') {
            $roles = array_values(array_intersect($roles, ['kaprodi', 'dekan', 'wd', 'kbk', 'lpm', 'baak', 'kepala_laboratorium']));
        }

        return ActorModuleRegistry::forRoles($roles);
    }
}
