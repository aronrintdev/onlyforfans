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

    public function run()
    {
        $this->faker = \Faker\Factory::create();

        // +++ Create admin users +++

        $this->createAdminUser([
            'name' => 'Peter G',
            'username' => 'peter',
            'email' => 'peter@peltronic.com',
            'password' => 'foo-123',
            'gender' => 'male',
            'city' => 'Las Vegas',
            'country' => 'US',
        ]);

        $this->createAdminUser([
            'name' => 'Matt M',
            'username' => 'mattm',
            'email' => 'matt@mjmwebdesign.com',
            'password' => 'foo-123',
            'gender' => 'male',
            'city' => 'Las Vegas',
            'country' => 'US',
        ]);

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

    private function createAdminUser($attrs)
    {
        dump('Creating user: '.$attrs['email']);
        $adminRole = Role::where('name','admin')->firstOrFail();
        $user = factory(User::class)->create();
        $user->email = $attrs['email'];
        $user->password = Hash::make($attrs['password']);
        $user->email_verified = 1;
        if ( array_key_exists('gender', $attrs) ) {
            $user->gender = $attrs['gender'];
        }
        if ( array_key_exists('city', $attrs) ) {
            $user->city = $attrs['city'];
        }
        if ( array_key_exists('country', $attrs) ) {
            $user->country = $attrs['country'];
        }
        $user->remember_token = str_random(10);
        $user->verification_code = str_random(18);
        $user->save();

        $user->roles()->attach($adminRole->id);

        $avatar = $this->createImage(MediafileTypeEnum::AVATAR);
        $cover = $this->createImage(MediafileTypeEnum::COVER);
        $timeline = $user->timeline;
        $timeline->username = $attrs['username'];
        $timeline->name = $attrs['name'];
        $timeline->avatar_id = $avatar->id;
        $timeline->cover_id = $cover->id;
        $timeline->save();

        unset($user, $timeline);
    }

    private function createImage($mftype) : ?Mediafile
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

        return $mediafile;
    }
}
