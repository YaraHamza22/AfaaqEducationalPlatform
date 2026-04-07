<?php

namespace Modules\UserMangementModule\Database\Seeders\RolesAndPermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AuditorRoleSeeder extends Seeder
{
    public function run(): void
    {
         $permissions = [
            'list-categories',
            'show-category',
            
            'list-courses',
            'show-course',

            //unit permissions
            'list-units',
            'show-unit',

            //lesson permissions
            'list-lessons',
            'show-lesson',
        ];
        
        $role = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'api']);
        $role->syncPermissions($permissions);
    }
}