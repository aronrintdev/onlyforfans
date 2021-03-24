<?php
namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Database\Seeders\TestDatabaseSeeder;
use DB;
use App;
use Tests\TestCase;
use App\Libs\FactoryHelpers;
use App\Models\User;
use App\Models\Post;
use Ramsey\Uuid\Uuid;
use App\Models\Mediafile;
use App\Enums\MediafileTypeEnum;

class MediafileModelTest extends TestCase
{
    use WithFaker; 
    //use RefreshDatabase; -- need to impl locally to disable during run-time %TODO

    /**
     * @group mediafile-model
     */
    // %NOTE: do not add to regressions as it needs to create actual images and upload to S3
    // $ APP_ENV=LOCAL  php  vendor/bin/phpunit --testdox  --group mediafile-model
    public function test_should_create_mediafile_then_thumbnail()
    {
        //$post = factory(Post::class)->make();
        $post = Post::firstOrFail();
        $mediafile = FactoryHelpers::createImage(
            MediafileTypeEnum::POST, // string $mftype,
            $post->id, // string $resourceID
            true, // $doS3Upload
            ['width'=>1280, 'height'=>720] // $attrs
        );
        //dd( $mediafile->filename, $mediafile->filepath, $mediafile->thumbFilename, $mediafile->thumbFilepath);
        $mediafile->createThumbnail();
        $mediafile->createMid();

        $mediafile = Mediafile::find($mediafile->id);
        $this->assertNotNull($mediafile);
        $this->assertSame(MediafileTypeEnum::POST,$mediafile->mftype);
        $this->assertTrue( Storage::disk('s3')->exists($mediafile->filename) );
        $this->assertTrue( $mediafile->has_thumb );
        $this->assertTrue( Storage::disk('s3')->exists($mediafile->thumbFilename) );
        $this->assertTrue( $mediafile->has_mid );
        $this->assertTrue( Storage::disk('s3')->exists($mediafile->midFilename) );

        // Test delete
        $mediafile->delete(); // should do soft delete
        $this->assertTrue( $mediafile->trashed() );
        $this->assertTrue( $mediafile->has_thumb );
        $this->assertTrue( $mediafile->has_mid );
        $this->assertTrue( Storage::disk('s3')->exists($mediafile->filename) );
        $this->assertTrue( Storage::disk('s3')->exists($mediafile->thumbFilename) );
        $this->assertTrue( Storage::disk('s3')->exists($mediafile->midFilename) );

        $mediafile->deleteAssets();
        $this->assertFalse( $mediafile->has_thumb );
        $this->assertFalse( $mediafile->has_mid );
        $this->assertFalse( Storage::disk('s3')->exists($mediafile->filename) );
        $this->assertFalse( Storage::disk('s3')->exists($mediafile->thumbFilename) );
        $this->assertFalse( Storage::disk('s3')->exists($mediafile->midFilename) );
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();

        if ( !App::environment(['testing']) ) {
            return;
        }

        $this->seed(TestDatabaseSeeder::class);

        // Update or add avatars to some users for this test...
        $users = User::take(5)->get();
        $users->each( function($u) {
            $avatar = FactoryHelpers::createImage(MediafileTypeEnum::AVATAR, null, false); //skip S3 upload
            $u->save();
        });
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}
