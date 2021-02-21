<?php
namespace Tests\Feature;

use DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\Story;
use App\Models\Mediafile;
use Illuminate\Http\File;

use App\Enums\MediafileTypeEnum;
use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Storage;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

// see: https://laravel.com/docs/5.4/http-tests#testing-file-uploads
// https://stackoverflow.com/questions/47366825/storing-files-to-aws-s3-using-laravel
// => https://stackoverflow.com/questions/29527611/laravel-5-how-do-you-copy-a-local-file-to-amazon-s3
// https://stackoverflow.com/questions/34455410/error-executing-putobject-on-aws-upload-fails
class MediafileTest extends TestCase
{
    // use DatabaseTransactions;
    use RefreshDatabase;
    use WithFaker;

    /**
     *  @group mediafiles
     *  @group regression
     */
    public function test_can_store_mediafile()
    {
        Storage::fake('s3');
        $filename = 'file-foo.png';

        $user = User::first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $payload = [
            'mediafile' => $file,
            'mftype' => MediafileTypeEnum::AVATAR,
        ];
        $response = $this->actingAs($user)->ajaxJSON('POST', route('mediafiles.store'), $payload);

        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafile = $content->mediafile;

        Storage::disk('s3')->assertExists($mediafile->filename);
        $this->assertSame($filename, $mediafile->mfname);
        $this->assertSame(MediafileTypeEnum::AVATAR, $mediafile->mftype);
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

