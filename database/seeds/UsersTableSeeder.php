<?php
use Illuminate\Database\Seeder;
//use Illuminate\Support\Facades\Storage;
//use Illuminate\Support\Str;

use App\Role;
use App\User;
use App\Libs\FactoryHelpers;
//use App\Mediafile;
//use App\Enums\MediafileTypeEnum;

class UsersTableSeeder extends Seeder
{

    public function run()
    {
        $this->command->info('Running Seeder: UsersTableSeeder...');

        $this->faker = \Faker\Factory::create();
        $adminRole = Role::where('name','admin')->firstOrFail();

        // +++ Create admin users +++

        //$this->createAdminUser([
        $user = factory(User::class)->create();
        FactoryHelpers::updateUser($user, [
            'name' => 'Peter G',
            'username' => 'peter',
            'email' => 'peter@peltronic.com',
            'password' => 'foo-123',
            'gender' => 'male',
            'city' => 'Las Vegas',
            'country' => 'US',
        ]);
        $user->roles()->attach($adminRole->id);
        unset($user);

        // --

        $user = factory(User::class)->create();
        FactoryHelpers::updateUser($user, [
            'name' => 'Matt M',
            'username' => 'mattm',
            'email' => 'matt@mjmwebdesign.com',
            'password' => 'foo-123',
            'gender' => 'male',
            'city' => 'Las Vegas',
            'country' => 'US',
        ]);
        $user->roles()->attach($adminRole->id);
        unset($user);

        //Populate dummy users
        //factory(User::class, 40)->create();

        // +++ Update default user settings +++

        $users = User::get();
        foreach ($users as $u) {
            DB::table('user_settings')->insert([
                'user_id'               => $u->id,
                'confirm_follow'        => 'no',
                'follow_privacy'        => 'everyone',
                'comment_privacy'       => 'everyone',
                'timeline_post_privacy' => 'everyone',
                'post_privacy'          => 'everyone',
                'message_privacy'       => 'everyone', 
            ]);

        }
    }
}
