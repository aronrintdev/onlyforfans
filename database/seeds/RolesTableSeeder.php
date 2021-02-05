<?php

namespace Database\Seeders;

use App\Role;
use App\Permission;

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
        /* --------------------------- SUPER ADMIN -------------------------- */
        /**
         * Super Admin Role access controlled by Gate in Auth service provider,
         * it does not need to have any permission associated with it.
         */
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $role->display_name = 'Super Admin';
        $role->description = 'Access to everything';
        $role->save();

        unset($role);

        /* ------------------------------ ADMIN ----------------------------- */
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->display_name = 'Admin';
        $role->description = 'Access limited to admin';
        $role->save();

        Permission::firstOrCreate(['name' => 'admin.*', 'guard_name' => 'web'])->update([
            'display_name' => 'All Admin Permissions',
            'description' => 'Access to any admin permission'
        ]);
        Permission::firstOrCreate(['name' => 'user.*.view', 'guard_name' => 'web'])->update([
            'display_name' => 'View Any User',
            'description' => 'Access to view any user'
        ]);

        $role->givePermissionTo([
            'admin.*',
            'User.view.*',
        ]);

        unset($role);

        /* ---------------------------- MODERATOR --------------------------- */
        $role = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $role->display_name = 'Moderator';
        $role->description = 'Access limited to moderator';
        $role->save();

        // TODO: Add Permissions



        unset($role);

        /* ----------------------------- EDITOR ----------------------------- */
        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $role->display_name = 'Editor';
        $role->description = 'Access limited to editor';
        $role->save();

        // TODO: Add Permissions

        unset($role);

        /* ------------------------------ USER ------------------------------ */
        $role = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $role->display_name = 'User';
        $role->description = 'Access limited to user';
        $role->save();

        // TODO: Add Permissions

        Permission::firstOrCreate(['name' => 'post.create', 'guard_name' => 'web'])->update([
            'display_name' => 'Create New Post',
            'description' => 'Access to Create a new Post'
        ]);

        $role->givePermissionTo([
            'Post.create',
        ]);

        unset($role);

        /* ------------------------------ USER ------------------------------ */
        $role = Role::firstOrCreate(['name' => 'banned', 'guard_name' => 'web']);
        $role->display_name = 'Banned User';
        $role->description = 'No Access';
        $role->save();

        unset($role);
    }
}
