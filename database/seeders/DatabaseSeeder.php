<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // // User Types
        // DB::table('usertypes')->insert([
        //     ['id' => 1, 'user_types' => 'Super Admin'],
        //     ['id' => 2, 'user_types' => 'Admin'],
        //     ['id' => 3, 'user_types' => 'Approver'],
        //     ['id' => 4, 'user_types' => 'User'],
        // ]);

        // // Users
        // DB::table('users')->insert([
        //     [
        //         'id' => 1,
        //         'first_name' => 'Stellar',
        //         'last_name' => 'Admin',
        //         'email' => 'contact@stellaripl.com',
        //         'password' => '$2a$12$H41TfDATavme0ID5TQIfZOOg6vGbmqvuVfnPltMEcSvJPfKVNFLeK',
        //         'user_type_id' => 1,
        //         'is_active' => 1,
        //     ],
        //     [
        //         'id' => 2,
        //         'first_name' => 'Ajith Kumar',
        //         'last_name' => 'M',
        //         'email' => 'ajith.km@stellaripl.com',
        //         'password' => '$2a$12$/FUDTq6rAfE0iTSvLcGbauzfp4vFd52U/e1mbm0846i7Nqf1ApDqK',
        //         'user_type_id' => 2,
        //         'is_active' => 1,
        //     ],
        //     [
        //         'id' => 3,
        //         'first_name' => 'Shanmugam',
        //         'last_name' => 'N',
        //         'email' => 'shanmugam@stellaripl.com',
        //         'password' => '$2a$12$ma4IuItmPS0q8ZL7gGTyZeIdowOv029tjhTHl9Stl889CF9d7FPqu',
        //         'user_type_id' => 2,
        //         'is_active' => 1,
        //     ],
        //     [
        //         'id' => 4,
        //         'first_name' => 'Prabhu Kumar',
        //         'last_name' => 'J',
        //         'email' => 'prabu.j@stellaripl.com',
        //         'password' => '$2a$12$woJRB5CKYkP17H2MpOTpCunABH/9kZJtFJauW4WYc2cb28F0TBbjq',
        //         'user_type_id' => 2,
        //         'is_active' => 1,
        //     ],
        //     [
        //         'id' => 5,
        //         'first_name' => 'Sharbudin',
        //         'last_name' => 'K',
        //         'email' => 'k.sharbudin@stellaripl.com',
        //         'password' => '$2a$12$woJRB5CKYkP17H2MpOTpCunABH/9kZJtFJauW4WYc2cb28F0TBbjq',
        //         'user_type_id' => 2,
        //         'is_active' => 1,
        //     ],
        //     [
        //         'id' => 6,
        //         'first_name' => 'Rajalakshmi',
        //         'last_name' => 'Mani',
        //         'email' => 'rajalakshmimani@stellaripl.com',
        //         'password' => '$2a$12$woJRB5CKYkP17H2MpOTpCunABH/9kZJtFJauW4WYc2cb28F0TBbjq',
        //         'user_type_id' => 2,
        //         'is_active' => 1,
        //     ],
        // ]);

        // // Roles
        // DB::table('roles')->insert([
        //     ['id' => 1, 'name' => 'Super Admin', 'guard_name' => 'web'],
        //     ['id' => 2, 'name' => 'Admin', 'guard_name' => 'web'],
        //     ['id' => 3, 'name' => 'Approver', 'guard_name' => 'web'],
        //     ['id' => 4, 'name' => 'User', 'guard_name' => 'web']
        // ]);

        // // Model Has Roles
        // DB::table('model_has_roles')->insert([
        //     ['role_id' => 1, 'model_type' => 'App\Models\User', 'model_id' => 1],
        //     ['role_id' => 2, 'model_type' => 'App\Models\User', 'model_id' => 2],
        //     ['role_id' => 2, 'model_type' => 'App\Models\User', 'model_id' => 3],
        //     ['role_id' => 2, 'model_type' => 'App\Models\User', 'model_id' => 4],
        //     ['role_id' => 2, 'model_type' => 'App\Models\User', 'model_id' => 5],
        //     ['role_id' => 2, 'model_type' => 'App\Models\User', 'model_id' => 6],
        // ]);

        // SQL file Import
        $files = [resource_path('sql/base_data.sql')];
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach($files as $file) {
            try {
                if (file_exists($file)) {
                    \DB::unprepared(
                        file_get_contents($file)
                    );
                    $this->command->info('SQL file executed successfully.');
                } else {
                    $this->command->error('SQL file not found.');
                }
            } catch (\Exception $e) {
                $this->command->error('An error occurred: ' . $e->getMessage());
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
