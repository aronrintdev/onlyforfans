<?php
namespace Database\Seeders;

use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Libs\FactoryHelpers;
use App\Enums\MediafileTypeEnum;
use App\Models\Role;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('UsersTableSeeder'); // $this->{output, faker, appEnv}

        // $adminRole = Role::where('name','admin')->firstOrFail();

        if ( $this->appEnv !== 'testing' ) { // if tests have pre-existing admins we'll need to make sure a random user chose is *not* an admin

            // +++ Create admin users +++
            $manualUsers = [
                [
                    'name' => 'Peter G',
                    'username' => 'peter',
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
                    'name' => 'Jeremy F',
                    'username' => 'jeremeyf',
                    'email' => 'jeremy.fall@contentmarketingleads.com',
                    'gender' => 'male',
                    'city' => 'Las Vegas',
                    'country' => 'US',
                    'is_follow_for_free' => 1, // if not free need to set price as well
                    'password' => bcrypt('foo-123'), // secret
                    'email_verified' => 1,
                ],
                [
                    'name' => 'Matt M',
                    'username' => 'mattm',
                    'email' => 'matt@mjmwebdesign.com',
                    'gender' => 'male',
                    'city' => 'Las Vegas',
                    'country' => 'US',
                    'is_follow_for_free' => 1, // if not free need to set price as well
                    'password' => bcrypt('foo-123'), // secret
                    'email_verified' => 1,
                ],
                [
                    'name' => 'Chad J',
                    'username' => 'chadj',
                    'email' => 'realchadjohnson@gmail.com',
                    'gender' => 'male',
                    'city' => 'Las Vegas',
                    'country' => 'US',
                    'is_follow_for_free' => 1, // if not free need to set price as well
                ],
            ];

            $this->output->writeln("  - Creating admin users...");

            foreach ($manualUsers as $u) {
                $user = User::where('email', $u['email'])->first();
                if ($user) {
                    $user->delete();
                }
                $user = FactoryHelpers::createUser($u);
                $user->assignRole('super-admin');
                unset($user);
            }
        } // testing

        // +++ Create non-admin users +++

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Creating non-admin users...");
        }

        $isFollowForFree = true;
        User::factory()->count($this->getMax('users'))->create()->each( function($u) use(&$isFollowForFree) {

            static $iter = 1;

            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("Creating new user with avatar & cover: " . $u->name." (iter: $iter)");
                $avatar = FactoryHelpers::createImage(MediafileTypeEnum::AVATAR, null, true);
                $cover = FactoryHelpers::createImage(MediafileTypeEnum::COVER, null, true);
            } else {
                //$this->output->writeln("Creating new user without avatar & cover: " . $u->name." (iter: $iter)");
                $avatar = null;
                $cover = null;
            }

            $u->save();

            $timeline = $u->timeline;
            $timeline->avatar_id = $avatar->id ?? null;
            $timeline->cover_id = $cover->id ?? null;
            $timeline->is_follow_for_free = $isFollowForFree;
            $timeline->price = $isFollowForFree ? 0 : $this->faker->randomFloat(2, 1, 300);
            $timeline->save();
            $isFollowForFree = !$isFollowForFree; // toggle so we get at least one of each

            $iter++;
        });

        // +++ Update default user settings +++

        // User::get()->each( function($u) {
        //     DB::table('user_settings')->insert([
        //         'user_id'               => $u->id,
        //         'confirm_follow'        => 'no',
        //         'follow_privacy'        => 'everyone',
        //         'comment_privacy'       => 'everyone',
        //         'timeline_post_privacy' => 'everyone',
        //         'post_privacy'          => 'everyone',
        //         'message_privacy'       => 'everyone',
        //     ]);
        // });
    }

    private function getMax($param) : int
    {
        static $max = [
            'testing' => [
                'users' => 7,
            ],
            'local' => [
                'users' => 50,
            ],
        ];
        return $max[$this->appEnv][$param];
    }
}
