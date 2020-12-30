<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Role;
use App\User;
use App\Mediafile;
use App\Enums\MediafileTypeEnum;

class UsersTableSeeder extends Seeder
{
    private function createImage($mftype)
    {
        $url = 'https://loremflickr.com/json/g/320/240/paris,girl,cat,dog/all';
        $json = json_decode(file_get_contents($url));
        $info = pathinfo($json->file);
        $ext = $info['extension'];
        $basename = $info['basename'];
        $s3Path = 'avatars/'.$basename;
        //$contents = file_get_contents('https://loremflickr.com/640/360');
        $contents = file_get_contents($json->file);
//dd($json, $info);

        //$path = 'avatars/'.$mediafile->slug.'.'.$mediafile->slug;
        Storage::disk('s3')->put($s3Path, $contents);
        //$s3File = Storage::disk('s3')->url($fn);
        $mimetype = (function($ext) {
            switch ($ext) {
                case 'jpeg':
                case 'jpg':
                    return 'image/jpeg';
                case 'png':
                    return 'image/png';
            }
        })($ext);

        $mediafile = Mediafile::create([
            'filename' => $s3Path,
            'mfname' => $slug = Str::slug($this->faker->catchPhrase,'-').'.'.$ext,
            'mftype' => $mftype, // MediafileTypeEnum::AVATAR
            'mimetype' => $mimetype, // $file->getMimeType(),
            'orig_filename' => $basename, // $file->getClientOriginalName(),
            'orig_ext' => $ext, // $file->getClientOriginalExtension(),
        ]);

    }

    public function run()
    {
        $this->faker = \Faker\Factory::create();

        $this->createImage(MediafileTypeEnum::AVATAR);
dd('done');

        $contents = file_get_contents('https://loremflickr.com/640/360');
        Storage::disk('s3')->put('tmp/name', $contents);



        $adminRole = Role::where('name','admin')->firstOrFail();
        // Lets create roles first
        //     $roles = array(
        //     array('name' => 'admin' ),
        //     array('name' => 'user' ),
        //     array('name' => 'moderator' )
        // );

        // Role::insert($roles);

        // [ ] attach role
        // [ ] settings

        // +++ Create admin users +++

        $user = factory(User::class)->create();
        $user->email = 'peter@peltronic.com';
        $user->password = Hash::make('foo-123');
        $user->remember_token = str_random(10);
        $user->verification_code = str_random(18);
        $user->email_verified = 1;
        $user->verified = 1;
        $user->gender = 'male';
        $user->city = 'Las Vegas';
        $user->country = 'US';
        $user->save();

        $user->roles()->attach($adminRole->id);

        $timeline = $user->timeline;
        $timeline->username = 'peter';
        $timeline->name = 'Peter G';
        //$timeline->avatar = file_get_contents('https://loremflickr.com/640/360');
        //$timeline->cover = %TODO: switch to Mediafile
        $timeline->save();

        unset($user, $timeline);

        // ---

        $user = factory(User::class)->create();
        $user->email = 'matt@mjmwebdesign.com';
        $user->password = Hash::make('foo-123');
        $user->remember_token = str_random(10);
        $user->verification_code = str_random(18);
        $user->email_verified = 1;
        $user->gender = 'male';
        $user->city = 'Las Vegas';
        $user->country = 'US';
        $user->save();

        $user->roles()->attach($adminRole->id);

        $timeline = $user->timeline;
        $timeline->username = 'mattm';
        $timeline->name = 'Matt M';
        $timeline->save();

        unset($user, $timeline);

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
