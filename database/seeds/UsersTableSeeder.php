<?php
use Illuminate\Database\Seeder;
//use Illuminate\Support\Facades\Storage;
//use Illuminate\Support\Str;

use App\Role;
use App\User;
use App\Libs\FactoryHelpers;
//use App\Mediafile;
use App\Enums\MediafileTypeEnum;

class UsersTableSeeder extends Seeder
{

    public function run()
    {
        $this->command->info('Running Seeder: UsersTableSeeder...');

        $this->faker = \Faker\Factory::create();
        $adminRole = Role::where('name','admin')->firstOrFail();

        // +++ Create admin users +++

        $user = factory(User::class)->create();
        FactoryHelpers::updateUser($user, [
            'name' => 'Peter G',
            'username' => 'peter',
            'email' => 'peter@peltronic.com',
            'gender' => 'male',
            'city' => 'Las Vegas',
            'country' => 'US',
            'is_follow_for_free' => 1,
        ]);
        $user->roles()->attach($adminRole->id);
        unset($user);

        // --

        $user = factory(User::class)->create();
        FactoryHelpers::updateUser($user, [
            'name' => 'Matt M',
            'username' => 'mattm',
            'email' => 'matt@mjmwebdesign.com',
            'gender' => 'male',
            'city' => 'Las Vegas',
            'country' => 'US',
            'is_follow_for_free' => 1, // if not free need to set price as well
        ]);
        $user->roles()->attach($adminRole->id);
        unset($user);

        // --

        $user = factory(User::class)->create();
        FactoryHelpers::updateUser($user, [
            'name' => 'Erik H',
            'username' => 'erikh',
            'email' => 'erik@hattervigsoftwaresolutions.com',
            'gender' => 'male',
            'city' => 'Rapid City',
            'country' => 'US',
            'is_follow_for_free' => 1, // if not free need to set price as well
        ]);
        $user->roles()->attach($adminRole->id);
        unset($user);

        // --

        $user = factory(User::class)->create();
        FactoryHelpers::updateUser($user, [
            'name' => 'Chad J',
            'username' => 'chadj',
            'email' => 'realchadjohnson@gmail.com',
            'gender' => 'male',
            'city' => 'Las Vegas',
            'country' => 'US',
            'is_follow_for_free' => 1, // if not free need to set price as well
        ]);
        $user->roles()->attach($adminRole->id);
        unset($user);

        // +++ Create non-admin users +++

        factory(User::class, 50)->create()->each( function($u) {
            $this->command->info("Adding avatar & cover for new user ".$u->name);
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
