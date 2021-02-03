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

use App\User;
use App\Story;
use App\Mediafile;

// see: https://laravel.com/docs/5.4/http-tests#testing-file-uploads
// https://stackoverflow.com/questions/47366825/storing-files-to-aws-s3-using-laravel
// => https://stackoverflow.com/questions/29527611/laravel-5-how-do-you-copy-a-local-file-to-amazon-s3
// https://stackoverflow.com/questions/34455410/error-executing-putobject-on-aws-upload-fails
class MediafileTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
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
            'mftype' => 'test',
        ];
        $response = $this->actingAs($user)->ajaxJSON('POST', route('mediafiles.store'), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->mediafile);
        $mediafile = $content->mediafile;

        Storage::disk('s3')->assertExists($mediafile->filename);
        $this->assertSame($filename, $mediafile->mfname);
        $this->assertSame('test', $mediafile->mftype);

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

