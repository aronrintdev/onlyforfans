<?php
namespace Database\Seeders\Production;

use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Seeder;

use Database\Seeders\SeederTraits;

use App\Libs\FactoryHelpers;
use App\Enums\MediafileTypeEnum;
use App\Models\Role;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    use SeederTraits;

    protected $doS3Upload = false;

    public function run()
    {
        $this->initSeederTraits('UsersTableSeeder'); // $this->{output, faker, appEnv}

        // $adminRole = Role::where('name','admin')->firstOrFail();

        $this->doS3Upload = false; // ( $this->appEnv !== 'testing' );

            $this->output->writeln("  - Creating admin users...");

            // +++ Create admin users +++
            $manualUsers = [
                [
                    'name' => 'Peter G',
                    'username' => 'peter',
                    'real_firstname' => 'Peter',
                    'real_lastname' => 'G',
                    'email' => 'peter@peltronic.com',
                    'gender' => 'male',
                    'city' => 'Las Vegas',
                    'country' => 'US',
                    'is_follow_for_free' => 1,
                    'password' => bcrypt('foo-123'), // secret
                    'email_verified' => 1,
                ],
                [
                    'name' => 'Erik H',
                    'real_firstname' => 'Erik',
                    'real_lastname' => 'H',
                    'username' => 'erikh',
                    'email' => 'erik@hattervigsoftwaresolutions.com',
                    'gender' => 'male',
                    'city' => 'Rapid City',
                    'country' => 'US',
                    'is_follow_for_free' => 1, // if not free need to set price as well
                    'password' => bcrypt('foo-123'), // secret
                    'email_verified' => 1,
                ],
                [
                    'name' => 'Niko A',
                    'real_firstname' => 'Niko',
                    'real_lastname' => 'A',
                    'username' => 'nikoa',
                    'email' => 'nikoanzai@gmail.com',
                    'gender' => 'male',
                    'city' => 'Tokyo',
                    'country' => 'Japan',
                    'is_follow_for_free' => 1, // if not free need to set price as well
                    'password' => bcrypt('foo-123'), // secret
                    'email_verified' => 1,
                ],
                [
                    'name' => 'Matt M',
                    'real_firstname' => 'Matt',
                    'real_lastname' => 'M',
                    'username' => 'mattm',
                    //'email' => 'matt@mjmwebdesign.com',
                    'email' => 'matt@allfans.com',
                    'gender' => 'male',
                    'city' => 'Las Vegas',
                    'country' => 'US',
                    'is_follow_for_free' => 1, // if not free need to set price as well
                    'password' => bcrypt('foo-123'), // secret
                    'email_verified' => 1,
                ],
                [
                    'name' => 'Chad J',
                    'real_firstname' => 'Chad',
                    'real_lastname' => 'J',
                    'username' => 'chadj',
                    //'email' => 'realchadjohnson@gmail.com',
                    'email' => 'chad@allfans.com',
                    'gender' => 'male',
                    'city' => 'Las Vegas',
                    'country' => 'US',
                    'is_follow_for_free' => 1, // if not free need to set price as well
                ],
                [
                    'name' => 'Fujio H',
                    'real_firstname' => 'Fujio',
                    'real_lastname' => 'H',
                    'username' => 'fujioh',
                    'email' => 'harouf@outlook.com',
                    'gender' => 'male',
                    'city' => 'Tokyo',
                    'country' => 'Japan',
                    'is_follow_for_free' => 1, // if not free need to set price as well
                    'password' => bcrypt('foo-123'), // secret
                    'email_verified' => 1,
                ],
            ];

            $this->output->writeln("  - Creating PRODUCTION admin users...");

            foreach ($manualUsers as $u) {
                $user = User::where('email', $u['email'])->first();
                if ($user) {
                    $user->delete();
                }
                $user = FactoryHelpers::createUser($u);
                $user->assignRole('super-admin');
                $user->assignRole('admin');
                unset($user);
            }
        } // testing

    }

