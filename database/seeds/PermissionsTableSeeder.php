<?php
namespace Database\Seeders;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('PermissionsTableSeeder'); // $this->{output, faker, appEnv}

        // $adminRole = Role::where('name','admin')->firstOrFail();

        $this->output->writeln("  - Creating staff permissions...");

        // +++ Create admin users +++
        $staffPermissions = [
            [
                'guard_name' => 'staff',
                'name' => 'Post.create',
                'display_name' => 'Create',
                'description' => 'Access to create posts for the creator',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Post.edit',
                'display_name' => 'Edit',
                'description' => 'Access to edit posts for the creator',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Post.delete',
                'display_name' => 'Delete',
                'description' => 'Access to remove posts for the creator',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Post.interact',
                'display_name' => 'Like/Unlike, comment, share',
                'description' => 'Access to interact(like, comment, share) with posts',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Story.create',
                'display_name' => 'Create',
                'description' => 'Access to create stories for creator',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Post.viewAll',
                'display_name' => 'View paid/subscriber-only posts',
                'description' => 'Access to see paid & subscriber-only posts',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Chat.message',
                'display_name' => 'Message',
                'description' => 'Access to message with followers & fans',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Vault.addFile',
                'display_name' => 'Add vault files',
                'description' => 'Access to add vault files',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Vault.deleteFile',
                'display_name' => 'Delete vault files',
                'description' => 'Access to delete vault files',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Vault.downloadFile',
                'display_name' => 'Download vault files',
                'description' => 'Access to download vault files',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Settings.managePaymentMethods',
                'display_name' => 'Manage payment methods',
                'description' => 'Access to manage payment methods',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Settings.viewAccountEarnings',
                'display_name' => 'View account earnings/sales data',
                'description' => 'Access to access and view account earnings/sales data',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Settings.manageFans',
                'display_name' => 'Block/unblock/restrict fans',
                'description' => 'Access to block/unblock/restrict fans',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Chat.edit',
                'display_name' => 'Edit/delete',
                'description' => 'Access to edit/delete messages',
            ]
        ];


        foreach ($staffPermissions as $p) {
            Permission::create($p);
        }

    }
}
