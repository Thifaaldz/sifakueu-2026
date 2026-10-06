<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Security\Permission;
use App\Models\Security\Role;
use App\Support\Access\AccessControl;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AccessControl::allPermissions() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = collect(AccessControl::ROLES)->mapWithKeys(fn (string $role) => [
            $role => Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']),
        ]);

        foreach ($roles as $roleName => $role) {
            $role->syncPermissions(AccessControl::permissionsForRole($roleName));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
