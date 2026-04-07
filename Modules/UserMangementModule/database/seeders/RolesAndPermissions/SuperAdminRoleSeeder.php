<?php

namespace Modules\UserMangementModule\Database\Seeders\RolesAndPermissions;

use Illuminate\Database\Seeder;
use Modules\UserMangementModule\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'api']);
        $permissions = Permission::where('guard_name','api')->get();
        $superAdminRole->syncPermissions($permissions);
     

        $superAdmins = [
            "yara@example.com",
            "karam@example.com"
        ];
        
        foreach($superAdmins as $admin){
             $superAdmin = User::firstOrCreate(['email' => $admin],
                [
                    "name" => "admin",
                    "email" => $admin,
                    "password" => "P@ssw0rd",
                    "phone" => "+963991554887",
                    "date_of_birth" => "2025-01-30",
                    "gender" => "male"
                ]);

            $superAdmin->assignRole($superAdminRole);
        }
       
    }
}