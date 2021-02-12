<?php
namespace App\Libs;

use Exception;
use App\Models\User;
use App\Models\Mediafile;
use Illuminate\Support\Str;
use Faker\Factory;

use App\Enums\MediafileTypeEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class FactoryHelpers {


    // Adds avatar & cover images
    public static function updateUser(&$user, $attrs)
    {
        //dump('Updating user: '.$attrs['email']);
        $user->email = $attrs['email'];
        $user->username = $attrs['username'];

        // if ( array_key_exists('gender', $attrs) ) {
        //     $user->gender = $attrs['gender'];
        // }
        // if ( array_key_exists('city', $attrs) ) {
        //     $user->city = $attrs['city'];
        // }
        // if ( array_key_exists('country', $attrs) ) {
        //     $user->country = $attrs['country'];
        // }
        $user->save();

        if ( Config::get('app.env') !== 'testing' ) {
            $avatar = self::createImage(MediafileTypeEnum::AVATAR);
            $cover = self::createImage(MediafileTypeEnum::COVER);
        } else {
            $avatar = null;
            $cover = null;
        }
        $timeline = $user->timeline;
        $timeline->name = $attrs['name'];
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
    }

    public static function parseRandomSubset(Collection $setIn, $MAX=10) : Collection
    {
        $faker = Factory::create();
        $_max = min([ $MAX, $setIn->count()-1  ]);
        $_num = $faker->numberBetween(0,$_max);
        $subset = $setIn->random($_num);
        return $subset;
    }

    // Inserts a [mediafiles] record
    public static function createImage(string $mftype, string $resourceID = null) : ?Mediafile
    {
        $faker = \Faker\Factory::create();

        // https://loremflickr.com/320/240/paris,girl,kitten,puppy,beach,rave
        //$url = 'https://loremflickr.com/json/320/240/paris,girl,kitten,puppy,beach,rave';
        $keyword = $faker->randomElement([ 'paris', 'girl', 'kitten', 'puppy', 'beach', 'rave' ]);
        $url = 'https://loremflickr.com/json/320/240';
        $url .= '/'.$keyword;
        $url .= '?random='.$faker->uuid;
        $json = json_decode(file_get_contents($url));
        $info = pathinfo($json->file);
        $ext = $info['extension'];
        $basename = $info['basename'].'-'.$faker->randomNumber(6,true);
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
            'orig_filename' => $basename, // $file->getClientOriginalName(),
            'orig_ext' => $ext, // $file->getClientOriginalExtension(),
        ];

        switch ($mftype) {
            case MediafileTypeEnum::AVATAR:
                $s3Path = 'avatars/'.$basename;
                break;
            case MediafileTypeEnum::COVER:
                $s3Path = 'covers/'.$basename;
                break;
            case MediafileTypeEnum::POST:
                $s3Path = 'posts/'.$basename;
                $attrs['resource_id'] =  $resourceID; // ie story_id: required for story type
                $attrs['resource_type'] = 'posts';
                break;
            case MediafileTypeEnum::STORY:
                $s3Path = 'stories/'.$basename;
                $attrs['resource_id'] =  $resourceID; // ie story_id: required for story type
                $attrs['resource_type'] = 'stories';
                break;
            default:
                throw new Exception('media file type of ' . $mftype . ' not supported');
        }
        $contents = file_get_contents($json->file);
        //dd($json, $info);

        Storage::disk('s3')->put($s3Path, $contents);
        $attrs['filename'] = $s3Path;

        $mediafile = Mediafile::create($attrs);

        return $mediafile;
    }

}
