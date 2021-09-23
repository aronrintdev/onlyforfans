<?php
namespace Database\Seeders;

use Exception;
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

    protected $doS3Upload = false;

    public function run()
    {
        $this->initSeederTraits('UsersTableSeeder'); // $this->{output, faker, appEnv}

        // $adminRole = Role::where('name','admin')->firstOrFail();

        $this->doS3Upload = ( $this->appEnv !== 'testing' );

        if ( $this->appEnv !== 'testing' ) { // if tests have pre-existing admins we'll need to make sure a random user chose is *not* an admin

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

            $this->output->writeln("  - Creating admin users...");

            foreach ($manualUsers as $u) {
                $user = User::where('email', $u['email'])->first();
                if ($user) {
                    $user->delete();
                }
                $user = FactoryHelpers::createUser($u); // admin user created manually
                $user->assignRole('super-admin');
                $user->assignRole('admin');
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
                try {
                    $avatar = FactoryHelpers::createImage( $u, MediafileTypeEnum::AVATAR, $u->id, $this->doS3Upload );
                } catch (Exception $e) {
                    if ( $this->appEnv !== 'testing' ) {
                        $this->output->writeln("  - Could not create fake media for user ".$u->name.", skipping - ".$e->getMessage() );
                    }
                }

                try {
                    $cover = FactoryHelpers::createImage( $u, MediafileTypeEnum::COVER, $u->id, $this->doS3Upload );
                } catch (Exception $e) {
                    if ( $this->appEnv !== 'testing' ) {
                        $this->output->writeln("  - Could not create fake media for user ".$u->name.", skipping - ".$e->getMessage() );
                    }
                }
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
            $isFree = $isFollowForFree ? false : $this->faker->numberBetween(0, 1);
            if (!$isFree) {
                $timeline->updateOneMonthPrice($timeline->castToMoney($this->faker->numberBetween(300, 10000)));
            }
            // $timeline->price = $isFollowForFree ? 0.00 : $this->faker->numberBetween(300, 10000);
            $timeline->save(); // update the timeline

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
                'users' => 18,
            ],
            'local' => [
                'users' => 50,
            ],
        ];
        return $max[$this->appEnv][$param];
    }
}
