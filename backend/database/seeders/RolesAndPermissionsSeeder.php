<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Pages
            'page-list',
            'page-create',
            'page-edit',
            'page-delete',

            // Menus
            'menu-list',
            'menu-create',
            'menu-edit',
            'menu-delete',

            // Users
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',

            // Roles
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',

            // Permissions
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $moderator = Role::firstOrCreate([
            'name' => 'Moderator',
            'guard_name' => 'web',
        ]);

        $editor = Role::firstOrCreate([
            'name' => 'Editor',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions(Permission::all());

        $moderator->syncPermissions([
            'page-list',
            'page-create',
            'page-edit',
            'menu-list',
        ]);

        $editor->syncPermissions([
            'page-list',
            'page-create',
            'page-edit',
            'menu-list',
        ]);
    }
}
