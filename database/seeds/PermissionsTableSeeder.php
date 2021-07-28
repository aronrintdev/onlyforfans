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
                'display_name' => 'Create posts for the creator',
                'description' => 'Access to create posts for the creator',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Post.edit',
                'display_name' => 'Edit posts for the creator',
                'description' => 'Access to edit posts for the creator',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Post.delete',
                'display_name' => 'Remove posts for the creator',
                'description' => 'Access to remove posts for the creator',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Post.interact',
                'display_name' => 'Interact(like, comment, share) with posts',
                'description' => 'Access to interact(like, comment, share) with posts',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Story.create',
                'display_name' => 'Create stories for creator',
                'description' => 'Access to create stories for creator',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Post.viewAll',
                'display_name' => 'See paid & subscriber-only posts',
                'description' => 'Access to see paid & subscriber-only posts',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Chat',
                'display_name' => 'Chat with followers & fans',
                'description' => 'Access to chat with followers & fans',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'VaultFile.add',
                'display_name' => 'Add vault files',
                'description' => 'Access to add vault files',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'VaultFile.delete',
                'display_name' => 'Delete vault files',
                'description' => 'Access to delete vault files',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'VaultFile.download',
                'display_name' => 'Download vault files',
                'description' => 'Access to download vault files',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'PaymentMethod.manage',
                'display_name' => 'Manage payment methods',
                'description' => 'Access to manage payment methods',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'AccountEarningsSales.view',
                'display_name' => 'Access and view account earnings/sales data',
                'description' => 'Access to access and view account earnings/sales data',
            ],
            [
                'guard_name' => 'staff',
                'name' => 'Fans.manage',
                'display_name' => 'Block/unblock/restrict users',
                'description' => 'Access to block/unblock/restrict users',
            ]
        ];


        foreach ($staffPermissions as $p) {
            Permission::create($p);
        }

    }
}
