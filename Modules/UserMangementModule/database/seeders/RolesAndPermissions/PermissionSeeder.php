<?php

namespace Modules\UserMangementModule\Database\Seeders\RolesAndPermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            //role permissions
            'create-roles',
            'update-roles',      
            'delete-roles',
            'list-roles',
            'show-roles',

            //users permissions (all users accounts)
            'create-user',
            'update-user',
            'delete-user',
            'list-users',
            'show-user',

            //students permissions 
            'create-student',
            'update-student',
            'delete-student',
            'list-students',
            'show-student',

            //instructor permissions
            'create-instructor',
            'update-instructor',
            'delete-instructor',
            'list-instructors',
            'show-instructor',

            //auditor permissions
            'create-auditor',
            'update-auditor',
            'delete-auditor',
            'list-auditors',
            'show-auditor',


            //course permissions
            'create-course',
            'update-course',
            'delete-course',
            'list-courses',
            'show-course',

            //course categories permissions
            'create-category',
            'update-category',
            'delete-category',
            'list-categories',
            'show-category',

            //unit permissions
            'create-unit',
            'update-unit',
            'delete-unit',
            'list-units',
            'show-unit',

            //lesson permissions
            'create-lesson',
            'update-lesson',
            'delete-lesson',
            'list-lessons',
            'show-lesson',

            // quiz permissions
            'create-quiz',
            'update-quiz',
            'delete-quiz',
            'list-quiz',
            'show-quiz',
            'publish-quiz',
            'unpublish-quiz',

            //question permissions
            'create-question',
            'update-question',
            'delete-question',
            'list-questions',
            'show-question',

            //question option permissions
            'create-option',
            'update-option',
            'delete-option',
            'list-options',
            'show-option',

            //attempt permissions
            'create-attempt',
            'update-attempt',
            'delete-attempt',
            'list-attempts',
            'show-attempt',
            'submit-attempt',
            'grade-attempt',

            //answer permissions
            'create-answer',
            'update-answer',
            'delete-answer',
            'list-answers',
            'show-answer',
            'submit-answer',

            //certificate permissions
            'create-certificate',
            'update-certificate',
            'delete-certificate',
            'list-certificates',
            'show-certificate',

            //review permissions
            'create-review',
            'update-review',
            'delete-review',
            'list-reviews',
            'show-review',

            //notification permissions
            'create-notification',

            //forum_thread permissions
            'create-thread_forum',
            'update-thread_forum',
            'delete-thread_forum',
            'list-threads_forum',
            'show-thread_forum',

            //forum_posts permissions
            'create-post_forum',
            'update-post_forum',
            'delete-post_forum',
            'list-posts_forum',
            'show-post_forum',

            //chat_message permissions
            'create-chat_message',
            'update-chat_message',
            'delete-chat_message',
            'list-chat_messages',
            'show-chat_message',

        ];

         

         foreach($permissions as $permission){
            Permission::create(['name'=>$permission , 'guard_name'=>'api']);
        }
    }
}