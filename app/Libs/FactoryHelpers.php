<?php
namespace App\Libs;

use Exception;
use App\Models\User;
use App\Models\MediaFile;
use Illuminate\Support\Str;

use App\Enums\MediaFileTypeEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class FactoryHelpers {


    // Adds avatar & cover images
    public static function updateUser(&$user, $attrs)
    {
        //dump('Updating user: '.$attrs['email']);
        $user->email = $attrs['email'];
        if ( array_key_exists('gender', $attrs) ) {
            $user->gender = $attrs['gender'];
        }
        if ( array_key_exists('city', $attrs) ) {
            $user->city = $attrs['city'];
        }
        if ( array_key_exists('country', $attrs) ) {
            $user->country = $attrs['country'];
        }
        $user->save();

        if ( Config::get('app.env') !== 'testing' ) {
            $avatar = self::createImage(MediaFileTypeEnum::AVATAR);
            $cover = self::createImage(MediaFileTypeEnum::COVER);
        } else {
            $avatar = null;
            $cover = null;
        }
        $timeline = $user->timeline;
        $timeline->username = $attrs['username'];
        $timeline->name = $attrs['name'];
        $timeline->avatar_id = $avatar->id ?? null;
        $timeline->cover_id = $cover->id ?? null;
        $timeline->save();

        //unset($user, $timeline);
    }

    public static function parseRandomSubset(Collection $setIn, $MAX=10) : Collection
    {
        $faker = \Faker\Factory::create();
        $_max = min([ $MAX, $setIn->count()-1  ]);
        $_num = $faker->numberBetween(0,$_max);
        $subset = $setIn->random($_num);
        return $subset;
    }

    // Inserts a [mediaFiles] record
    public static function createImage(string $type, ?int $resourceID=null) : ?MediaFile
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
            'name' => Str::slug($faker->catchPhrase,'-').'.'.$ext,
            'type' => $type,
            'mimetype' => $mimetype, // $file->getMimeType(),
            'orig_filename' => $basename, // $file->getClientOriginalName(),
            'orig_ext' => $ext, // $file->getClientOriginalExtension(),
        ];

        switch ($type) {
            case MediaFileTypeEnum::AVATAR:
                $s3Path = 'avatars/'.$basename;
                break;
            case MediaFileTypeEnum::COVER:
                $s3Path = 'covers/'.$basename;
                break;
            case MediaFileTypeEnum::POST:
                $s3Path = 'posts/'.$basename;
                $attrs['resource_id'] =  $resourceID; // ie story_id: required for story type
                $attrs['resource_type'] = 'posts';
                break;
            case MediaFileTypeEnum::STORY:
                $s3Path = 'stories/'.$basename;
                $attrs['resource_id'] =  $resourceID; // ie story_id: required for story type
                $attrs['resource_type'] = 'stories';
                break;
            default:
                throw new Exception('media file type of ' . $type . ' not supported');
        }
        $contents = file_get_contents($json->file);
        //dd($json, $info);

        Storage::disk('s3')->put($s3Path, $contents);
        $attrs['filename'] = $s3Path;

        $mediaFile = MediaFile::create($attrs);

        return $mediaFile;
    }

}
