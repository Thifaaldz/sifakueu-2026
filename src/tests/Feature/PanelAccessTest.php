<?php

use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Filament\Facades\Filament;

it('opens every seeded panel login route on the expected host', function () {
    $this->withServerVariables([
        'HTTP_HOST' => 'sifakueu.test',
        'HTTPS' => 'on',
    ])->get('/admin/login')->assertOk();

    foreach (['admin', 'dosen', 'mahasiswa', 'pimpinan'] as $path) {
        $this->withServerVariables([
            'HTTP_HOST' => 'fasilkom.sifakueu.test',
            'HTTPS' => 'on',
        ])->get("/{$path}/login")->assertOk();
    }
});

it('allows each seeded actor role to access its assigned panel', function (string $role, string $panelId) {
    app(TenantContext::class)->set(new Tenant(['slug' => 'fasilkom']));

    $user = panelAccessUser([$role]);
    $panel = Filament::getPanel($panelId);

    expect($user->canAccessPanel($panel))->toBeTrue();

    app(TenantContext::class)->clear();
})->with([
    ['admin_fakultas', 'admin'],
    ['admin_prodi', 'admin'],
    ['dosen', 'dosen'],
    ['dosen_pa', 'dosen'],
    ['dosen_pembimbing', 'dosen'],
    ['dosen_penguji', 'dosen'],
    ['mahasiswa', 'mahasiswa'],
    ['kaprodi', 'pimpinan'],
    ['dekan', 'pimpinan'],
    ['wd', 'pimpinan'],
    ['kbk', 'pimpinan'],
    ['lpm', 'pimpinan'],
    ['baak', 'pimpinan'],
    ['kepala_laboratorium', 'pimpinan'],
]);

it('allows one lecturer account to carry all lecturer access roles', function () {
    app(TenantContext::class)->set(new Tenant(['slug' => 'fasilkom']));

    $user = panelAccessUser(['dosen', 'dosen_pa', 'dosen_pembimbing', 'dosen_penguji']);
    $panel = Filament::getPanel('dosen');

    expect($user->canAccessPanel($panel))->toBeTrue();

    app(TenantContext::class)->clear();
});

it('allows the central seeded super admin only on the central panel', function () {
    app(TenantContext::class)->clear();

    $user = panelAccessUser(['super_admin']);
    $panel = Filament::getPanel('super-admin');

    expect($user->canAccessPanel($panel))->toBeTrue();
});

function panelAccessUser(array $roles): User
{
    return new class ($roles) extends User {
        private array $panelAccessRoles = [];

        public function __construct(array $roles = [])
        {
            $this->panelAccessRoles = $roles;

            parent::__construct([
                'email' => 'panel-access@example.test',
                'name' => 'Panel Access User',
                'status' => 'active',
            ]);
        }

        public function hasRole($roles, ?string $guard = null): bool
        {
            $roles = is_array($roles) ? $roles : [$roles];

            return filled(array_intersect($this->panelAccessRoles, $roles));
        }

        public function hasAnyRole(...$roles): bool
        {
            $roles = count($roles) === 1 && is_array($roles[0]) ? $roles[0] : $roles;

            return $this->hasRole($roles);
        }
    };
}
