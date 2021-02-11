<?php
namespace Tests\Feature;

use DB;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\User;
use App\Models\Story;
use App\Models\MediaFile;
use App\Enums\MediaFileTypeEnum;

// see: https://laravel.com/docs/5.4/http-tests#testing-file-uploads
// https://stackoverflow.com/questions/47366825/storing-files-to-aws-s3-using-laravel
// => https://stackoverflow.com/questions/29527611/laravel-5-how-do-you-copy-a-local-file-to-amazon-s3
// https://stackoverflow.com/questions/34455410/error-executing-putobject-on-aws-upload-fails
class MediaFileTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group mediaFiles
     *  @group regression
     */
    public function test_can_store_mediaFile()
    {
        Storage::fake('s3');
        $filename = 'file-foo.png';

        $user = User::first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $payload = [
            'mediaFile' => $file,
            'type' => MediaFileTypeEnum::AVATAR,
        ];
        $response = $this->actingAs($user)->ajaxJSON('POST', route('mediaFiles.store'), $payload);

        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->mediaFile);
        $mediaFile = $content->mediaFile;

        Storage::disk('s3')->assertExists($mediaFile->filename);
        $this->assertSame($filename, $mediaFile->name);
        $this->assertSame(MediaFileTypeEnum::AVATAR, $mediaFile->type);

        //dd($response['cart']->toArray());
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

