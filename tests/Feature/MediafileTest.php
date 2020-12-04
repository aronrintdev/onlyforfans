<?php
namespace Tests\Feature;

//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;

use App\User;
use App\Story;
use App\Mediafile;

//use UsstatesTableSeeder;
//use CategoriesTableSeeder;
//use MilestonesTableSeeder;

//use App\Models\User;
//use App\Models\Mediafile;
//use App\Enums\AccounttypeEnum;

// see: https://laravel.com/docs/5.4/http-tests#testing-file-uploads
// https://stackoverflow.com/questions/47366825/storing-files-to-aws-s3-using-laravel
// => https://stackoverflow.com/questions/29527611/laravel-5-how-do-you-copy-a-local-file-to-amazon-s3
// https://stackoverflow.com/questions/34455410/error-executing-putobject-on-aws-upload-fails
class MediafileTest extends TestCase
{
    //private $admin;
    private $sessionUser = null;

    /**
     *  @group OFF_mfdev
     */
    public function test_can_upload_image_file()
    {
        $sessionUser = $this->sessionUser;
        $story = $sessionUser->timeline->stories()->first();
        if ( !$story ) {
            $story = factory(\App\Story::class)->create([
                'timeline_id' => $sessionUser->timeline->id,
            ]);
        }
        //dd($sessionUser->toArray(), $story->toArray());
        //$file = UploadedFile::fake()->image('file-foo.png', 400, 400);
        $file = UploadedFile::fake()->image('file-foo.png', 400, 400);
        $payload = [
            'mediafile' => $file,
            'mftype' => 'test',
            'resource_id'=>$story->id,
            'resource_type'=>'stories',
        ];
        //$url = route('mediafiles.store');
        //$response = $this->actingAs($sessionUser)->json('POST', route('mediafiles.store'), $payload);
        $response = $this->actingAs($sessionUser)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(200);

        //dd($response['cart']->toArray());
    }

    protected function setUp() : void
    {
        parent::setUp();
        $user = User::first();
        if ( !$user || !$user->timeline ) { // setup a user w/ timeline if none exists
            $user = factory(\App\User::class)->create();
        }
        $this->sessionUser = $user;
        //$this->setupKits();
    }
}

