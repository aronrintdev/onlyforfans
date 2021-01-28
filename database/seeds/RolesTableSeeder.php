<?php

namespace Database\Seeders;

use App\Role;

class RolesTableSeeder extends Seeder
{
    /** Run in all environments */
    protected $environments = [ 'all' ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $role = Role::firstOrNew(['name' => 'super-admin', 'guard_name' => 'web']);
        $role->display_name = 'Super Admin';
        $role->description = 'Access to everything';
        $role->save();

        //Create admin role
        $role = Role::firstOrNew(['name' => 'admin', 'guard_name' => 'web']);
        $role->display_name = 'Admin';
        $role->description = 'Access limited to admin';
        $role->save();

        //Create user role
        $role = Role::firstOrNew(['name' => 'user', 'guard_name' => 'web']);
        $role->display_name = 'User';
        $role->description = 'Access limited to user';
        $role->save();

        //Create moderator role
        $role = Role::firstOrNew(['name' => 'moderator', 'guard_name' => 'web']);
        $role->display_name = 'Moderator';
        $role->description = 'Access limited to moderator';
        $role->save();

        //Create editor role
        $role = Role::firstOrNew(['name' => 'editor', 'guard_name' => 'web']);
        $role->display_name = 'Editor';
        $role->description = 'Access limited to editor';
        $role->save();
    }
}
