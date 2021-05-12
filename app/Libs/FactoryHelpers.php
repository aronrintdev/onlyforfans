<?php
namespace App\Libs;

use Exception;
use App\Models\User;
use App\Models\Timeline;
use App\Models\Mediafile;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

use App\Enums\MediafileTypeEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class FactoryHelpers {

    // Adds avatar & cover images
    public static function createUser(array $attrs) : User
    {
        $faker = Faker::create();

        $user = User::create([
            'username' => $attrs['username'],
            'email' => $attrs['email'],
            'password' => array_key_exists('password', $attrs) ? $attrs['password'] : bcrypt('foo-123'), // secret
            'email_verified' => 1,
        ]);
        //dump('Updating user: '.$attrs['email']);

        $isFollowForFree  = $attrs['is_follow_for_free'];
        $timeline = Timeline::create([
            'user_id'  => $user->id,
            'name'     => $attrs['name'],
            'about'    => $faker->text,
            'verified' => 1,
            'is_follow_for_free' => $isFollowForFree,
            'price' => $isFollowForFree ? 0.00 : $faker->randomFloat(2, 1, 300),
        ]);

        if ( Config::get('app.env') !== 'testing' ) {
            $avatar = self::createImage(MediafileTypeEnum::AVATAR, null, true);
            $cover = self::createImage(MediafileTypeEnum::COVER, null, true);
        } else {
            $avatar = null;
            $cover = null;
        }
        $timeline->avatar_id = $avatar->id ?? null;
        $timeline->cover_id = $cover->id ?? null;
        if ( array_key_exists('is_follow_for_free', $attrs) ) {
            $timeline->is_follow_for_free = $attrs['is_follow_for_free'];
        }
        if (array_key_exists('price', $attrs)) {
            $timeline->price = $attrs['price'];
        }
        if (array_key_exists('currency', $attrs)) {
            $timeline->currency = $attrs['currency'];
        }

        $timeline->save();
        $user->refresh();

        return $user;
    }

    public static function parseRandomSubset(Collection $setIn, $MAX=10) : Collection
    {
        $faker = Faker::create();
        $_max = min([ $MAX, $setIn->count()-1  ]);
        $_num = $faker->numberBetween(0,$_max);
        $subset = $setIn->random($_num);
        return $subset;
    }

    // Inserts a [mediafiles] record
    public static function createImage(
        string $mftype, 
        string $resourceID = null, 
        $doS3Upload=false,
        $attrs=[]
    ) : ?Mediafile
    {
        $faker = Faker::create();
if ($doS3Upload) {
    //dd('STOP');
}

        // https://loremflickr.com/320/240/paris,girl,kitten,puppy,beach,rave
        //$url = 'https://loremflickr.com/json/320/240/paris,girl,kitten,puppy,beach,rave';
        $keyword = $faker->randomElement([ 'paris', 'girl', 'kitten', 'puppy', 'beach', 'rave' ]);
        $width = $attrs['width'] ?? 320;
        $height = $attrs['height'] ?? 240;
        $url = "https://loremflickr.com/json/$width/$height";
        $url .= '/'.$keyword;
        $url .= '?random='.$faker->uuid;
        $json = json_decode(file_get_contents($url));
        $info = pathinfo($json->file);
        $ext = $info['extension'];
        $fnameToStore = parse_filebase($info['basename']).'-'.$faker->randomNumber(6,true).'.'.$ext;
        $mimetype = (function($ext) {
            switch ($ext) {
                case 'jpeg':
                case 'jpg':
                    return 'image/jpeg';
                case 'png':
                    return 'image/png';
            }
        })($ext);

        $attrs = [
            'mfname' => Str::slug($faker->catchPhrase,'-').'.'.$ext,
            'mftype' => $mftype,
            'mimetype' => $mimetype, // $file->getMimeType(),
            'orig_filename' => $fnameToStore, // $file->getClientOriginalName(),
            'orig_ext' => $ext, // $file->getClientOriginalExtension(),
        ];

        $subFolder = MediafileTypeEnum::getSubfolder($mftype);
        $s3Path = "$subFolder/$fnameToStore";

        switch ($mftype) {
            case MediafileTypeEnum::COVER:
            case MediafileTypeEnum::AVATAR:
                $attrs['resource_id'] =  $resourceID;
                $attrs['resource_type'] = 'users';
                break;
            case MediafileTypeEnum::POST:
                $attrs['resource_id'] =  $resourceID; // ie story_id: required for story type
                $attrs['resource_type'] = 'posts';
                break;
            case MediafileTypeEnum::STORY:
                $attrs['resource_id'] =  $resourceID; // ie story_id: required for story type
                $attrs['resource_type'] = 'stories';
                break;
            default:
                throw new Exception('media file type of ' . $mftype . ' not supported');
        }

        if ($doS3Upload) {
            // https://stackoverflow.com/questions/15076819/file-get-contents-ignoring-verify-peer-false
            $contents = file_get_contents($json->file);
            Storage::disk('s3')->put($s3Path, $contents);
            $attrs['filename'] = $s3Path;
        } else {
            $attrs['filename'] = $attrs['mfname']; // dummy filename for testing, etc
        }

        $mediafile = Mediafile::create($attrs);

        return $mediafile;
    }

}
