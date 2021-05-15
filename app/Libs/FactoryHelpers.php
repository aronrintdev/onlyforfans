<?php
namespace App\Libs;

use Exception;
use App\Models\User;
use App\Models\Timeline;
use App\Models\Mediafile;
use App\Models\DiskMediafile;
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
            $avatar = self::createImage($user, MediafileTypeEnum::AVATAR, null, true);
            $cover = self::createImage($user, MediafileTypeEnum::COVER, null, true);
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
        User $owner,
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
        if ($doS3Upload) {
            $url .= '?random='.$faker->uuid;
            $json = json_decode(file_get_contents($url));
            $info = pathinfo($json->file);
        } else {
            $url .= '/'.$faker->uuid.'.png';
            $info = pathinfo($url);
        }
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

        $dmf_attrs = [
            'mimetype' => $mimetype, // $file->getMimeType(),
            'orig_filename' => $fnameToStore, // $file->getClientOriginalName(),
            'orig_ext' => $ext, // $file->getClientOriginalExtension(),
            'resource_id' =>  null, // set below
            'resource_type' => null, // set below
            'filepath' => null, // set below
        ];

        $mfname = Str::slug($faker->catchPhrase,'-').'.'.$ext;
        $mf_attrs = [
            'diskmediafile_id' => null,
            'mfname' => $mfname,
            'mftype' => $mftype,
        ];

        $subFolder = $owner->id;
        $s3Path = "$subFolder/$fnameToStore";

        switch ($mftype) {
            case MediafileTypeEnum::COVER:
            case MediafileTypeEnum::AVATAR:
                $dmf_attrs['resource_id'] =  $resourceID;
                $dmf_attrs['resource_type'] = 'users';
                break;
            case MediafileTypeEnum::POST:
                $dmf_attrs['resource_id'] =  $resourceID; // ie story_id: required for story type
                $dmf_attrs['resource_type'] = 'posts';
                break;
            case MediafileTypeEnum::STORY:
                $dmf_attrs['resource_id'] =  $resourceID; // ie story_id: required for story type
                $dmf_attrs['resource_type'] = 'stories';
                break;
            default:
                throw new Exception('media file type of ' . $mftype . ' not supported');
        }

        if ($doS3Upload) {
            // https://stackoverflow.com/questions/15076819/file-get-contents-ignoring-verify-peer-false
            $contents = file_get_contents($json->file);
            Storage::disk('s3')->put($s3Path, $contents);
            $dmf_attrs['filepath'] = $s3Path;
        } else {
            $dmf_attrs['filepath'] = $mf_attrs['mfname']; // dummy filename/path for testing, etc
        }

        $mediafile = Diskmediafile::doCreate(
            $dmf_attrs['filepath'],        // $s3Filepath
            $mf_attrs['mfname'],           // $mfname
            $mf_attrs['mftype'],           // $mftype
            $owner,                        // $owner
            $dmf_attrs['resource_id'],     // $resourceID
            $dmf_attrs['resource_type'],   // $resourceType
            $dmf_attrs['mimetype'],        // $mimetype
            $dmf_attrs['orig_filename'],   // $origFilename
            $dmf_attrs['orig_ext'],        // $origExt
        );

        /*
        //$mediafile = DB::transaction(function () use($mf_attrs, $dmf_attrs) {
            //$diskmediafile = Diskmediafile::create($dmf_attrs);
            //$mf_attrs['diskmediafile_id'] = $diskmediafile->id;
            //$mediafile = Mediafile::create($mf_attrs);
            //return $mediafile;
        //});
         */

        return $mediafile;
    }

}
