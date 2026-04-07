<?php

namespace Modules\UserMangementModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\UserMangementModule\Database\Seeders\RolesAndPermissions\PermissionSeeder;
use Modules\UserMangementModule\Database\Seeders\RolesAndPermissions\SuperAdminRoleSeeder;
use Modules\UserMangementModule\Database\Seeders\RolesAndPermissions\AdminRoleSeeder;
use Modules\UserMangementModule\Database\Seeders\RolesAndPermissions\InstructorRoleSeeder;
use Modules\UserMangementModule\Database\Seeders\RolesAndPermissions\StudentRoleSeeder;    
use Modules\UserMangementModule\Database\Seeders\RolesAndPermissions\AuditorRoleSeeder;

class UserMangementModuleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $this->call([
            PermissionSeeder::class,
            SuperAdminRoleSeeder::class,
            AdminRoleSeeder::class,
            StudentRoleSeeder::class,
            InstructorRoleSeeder::class,
            AuditorRoleSeeder::class,
         ]);
    }
}
