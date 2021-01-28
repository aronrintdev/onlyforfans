<?php

namespace Database\Seeders;

use App\Role;
use App\User;
use App\Libs\FactoryHelpers;
//use App\Mediafile;
use App\Enums\MediafileTypeEnum;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{

    public function run()
    {
        $this->command->info('Running Seeder: UsersTableSeeder...');

        $this->faker = \Faker\Factory::create();
        // $adminRole = Role::where('name','admin')->firstOrFail();

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

        $user = User::where('email', 'matt@mjmwebdesign.com')->first();
        if (!$user) {
            $user = User::factory()->create();
        }
        $user = User::factory()->create();
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

        $user = User::where('email', 'erik@hattervigsoftwaresolutions.com')->first();
        if (!$user) {
            $user = User::factory()->create();
        }
        $user = User::factory()->create();
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

        $user = User::where('email', 'realchadjohnson@gmail.com')->first();
        if (!$user) {
            $user = User::factory()->create();
        }
        $user = User::factory()->create();
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

        // +++ Create non-admin users +++

        User::factory()->count(50)->create()->each( function($u) {
            $this->command->info("Adding avatar & cover for new user " . $u->name);
            $avatar = FactoryHelpers::createImage(MediafileTypeEnum::AVATAR);
            $cover = FactoryHelpers::createImage(MediafileTypeEnum::COVER);
            $timeline = $u->timeline;
            $timeline->avatar_id = $avatar->id;
            $timeline->cover_id = $cover->id;
            $timeline->save();
        });

        // +++ Update default user settings +++

        //$users = User::get();
        //foreach ($users as $u) {
        User::get()->each( function($u) {
            DB::table('user_settings')->insert([
                'user_id'               => $u->id,
                'confirm_follow'        => 'no',
                'follow_privacy'        => 'everyone',
                'comment_privacy'       => 'everyone',
                'timeline_post_privacy' => 'everyone',
                'post_privacy'          => 'everyone',
                'message_privacy'       => 'everyone',
            ]);
        });
    }
}
