<?php

namespace Modules\UserMangementModule\Database\Seeders\RolesAndPermissions;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\UserMangementModule\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::findOrCreate('super-admin', 'api');
        $permissions = Permission::where('guard_name', 'api')->get();
        $superAdminRole->syncPermissions($permissions);
     

        $superAdmins = [
            "yara@example.com",
            "karam@example.com"
        ];
        
        foreach ($superAdmins as $admin) {
            $superAdmin = User::updateOrCreate(['email' => $admin],
                [
                    "name" => "admin",
                    "email" => $admin,
                    "password" => Hash::make("P@ssw0rd"),
                    "phone" => "+963991554887",
                    "date_of_birth" => "2025-01-30",
                    "gender" => "male"
                ]);

            // Keep super-admin role consistent and avoid stale permission cache issues.
            $superAdmin->syncRoles([$superAdminRole->name]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}