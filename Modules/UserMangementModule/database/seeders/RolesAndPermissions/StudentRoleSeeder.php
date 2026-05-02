<?php

namespace Modules\UserMangementModule\Database\Seeders\RolesAndPermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class StudentRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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

            // quiz permissions
            'list-quiz',
            'show-quiz',

            //question permissions
            'list-questions',
            'show-question',

            //question option permissions
            'list-options',
            'show-option',

            //attempt permissions (full CRUD + lifecycle; aligns with AttemptController)
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

            //forum_thread permissions
            'list-threads_forum',
            'show-thread_forum',        
            'create-thread_forum',  
            'update-thread_forum',
            
            //forum_posts permissions
            'list-posts_forum',
            'show-post_forum',
            'create-post_forum',    
            'update-post_forum',

            //chat_message permissions
            'create-chat_message',
            'update-chat_message',
            'delete-chat_message',
            'list-chat_messages',
            'show-chat_message',

   
        ];

        $role = Role::firstOrCreate(['name' => 'student','guard_name'=>'api']);
        $role->syncPermissions($permissions);
    }
}