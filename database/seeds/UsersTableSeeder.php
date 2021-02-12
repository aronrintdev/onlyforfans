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

        if ( $this->appEnv !== 'testing' ) {

            // +++ Create admin users +++

            $user = User::where('email', 'peter@peltronic.com')->first();
            if (!$user) {
                $user = User::factory()->create();
            }
            FactoryHelpers::updateUser($user, [
                'name' => 'Peter G',
                'username' => 'peter',
                'email' => 'peter@peltronic.com',
                'gender' => 'male',
                'city' => 'Las Vegas',
                'country' => 'US',
                'is_follow_for_free' => 1,
            ]);
            $user->assignRole('super-admin');
            unset($user);

            // --

            $user = User::where('email', 'erik@hattervigsoftwaresolutions.com')->first();
            if (!$user) {
                $user = User::factory()->create();
            }
            FactoryHelpers::updateUser($user, [
                'name' => 'Erik H',
                'username' => 'erikh',
                'email' => 'erik@hattervigsoftwaresolutions.com',
                'gender' => 'male',
                'city' => 'Rapid City',
                'country' => 'US',
                'is_follow_for_free' => 1, // if not free need to set price as well
            ]);
            $user->assignRole('super-admin');
            unset($user);

            // --

            $user = User::where('email', 'matt@mjmwebdesign.com')->first();
            if (!$user) {
                $user = User::factory()->create();
            }
            FactoryHelpers::updateUser($user, [
                'name' => 'Matt M',
                'username' => 'mattm',
                'email' => 'matt@mjmwebdesign.com',
                'gender' => 'male',
                'city' => 'Las Vegas',
                'country' => 'US',
                'is_follow_for_free' => 1, // if not free need to set price as well
            ]);
            $user->assignRole('super-admin');
            unset($user);

            // --

            $user = User::where('email', 'realchadjohnson@gmail.com')->first();
            if (!$user) {
                $user = User::factory()->create();
            }
            FactoryHelpers::updateUser($user, [
                'name' => 'Chad J',
                'username' => 'chadj',
                'email' => 'realchadjohnson@gmail.com',
                'gender' => 'male',
                'city' => 'Las Vegas',
                'country' => 'US',
                'is_follow_for_free' => 1, // if not free need to set price as well
            ]);
            $user->assignRole('super-admin');
            unset($user);

        }

        // +++ Create non-admin users +++

        $isFollowForFree = true;
        User::factory()->count($this->getMax('users'))->create()->each( function($u) use(&$isFollowForFree) {
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("Adding avatar & cover for new user " . $u->name);
                $avatar = FactoryHelpers::createImage(MediafileTypeEnum::AVATAR);
                $cover = FactoryHelpers::createImage(MediafileTypeEnum::COVER);
            } else {
                $avatar = null;
                $cover = null;
            }

            $u->save();

            $timeline = $u->timeline;
            $timeline->avatar_id = $avatar->id ?? null;
            $timeline->cover_id = $cover->id ?? null;
            $timeline->is_follow_for_free = $isFollowForFree;
            $timeline->save();
            $isFollowForFree = !$isFollowForFree; // toggle so we get at least one of each
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
