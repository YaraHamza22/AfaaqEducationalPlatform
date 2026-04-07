<?php

namespace Modules\UserMangementModule\Database\Seeders\RolesAndPermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Modules\UserMangementModule\Models\User;
use Spatie\Permission\Models\Permission;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'api',
        ]);
        $permissions = Permission::where('guard_name','api')->get();
        $adminRole->syncPermissions($permissions);

        $permissions = [
            'create-user',
            'update-user',
            'delete-user',
            'list-users',
            'show-user',

            'list-roles',
            'show-role',

            'list-students',
            'show-student',
            'create-student',
            'update-student',
            'delete-student',

            'list-instructors',
            'show-instructor',
            'create-instructor',
            'update-instructor',
            'delete-instructor',

            'list-auditors',
            'show-auditor',
            'create-auditor',
            'update-auditor',
            'delete-auditor',

            'create-category',

            'list-courses',
            'show-course',
            'create-course',
            'update-course',
            'delete-course',
            'publish-course',

            'list-units',
            'show-unit',
            'create-unit',
            'update-unit',
            'delete-unit',

            'list-lessons',
            'show-lesson',
            'create-lesson',
            'update-lesson',
            'delete-lesson',

            'list-quiz',
            'show-quiz',
            'create-quiz',
            'update-quiz',
            'delete-quiz',

            'list-questions',
            'show-question',
            'create-question',
            'update-question',
            'delete-question',

            'list-options',
            'show-option',
            'create-option',
            'update-option',
            'delete-option',

            'create-attempt',
            'show-attempt',

            'create-answer',
            'update-answer',
            'delete-answer',
            'list-answers',
            'show-answer',
        ];



        $admins = [
            'admin@example.com',
        ];

        foreach ($admins as $adminEmail) {
            $admin = User::firstOrCreate (

                ['email' => $adminEmail],
                [
                    'name' => 'Admin',
                    'email' => $adminEmail,
                    'password' => 'Admin@123456',
                    'phone' => '+963991554554',
                    'date_of_birth' => '2000-01-01',
                    'gender' => 'male',
                ]
            );

                $admin->assignRole($adminRole);
            }
        }
    }
