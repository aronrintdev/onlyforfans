<?php
namespace Tests\Unit;

use DB;
use App;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\TestDatabaseSeeder;
use Tests\TestCase;
use App\Libs\FactoryHelpers;
use App\Models\User;
use App\Models\Post;
use Ramsey\Uuid\Uuid;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Enums\MediafileTypeEnum;

class MediafileModelTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * @group mediafile-model
     * @group OFF-here0515
     */
    public function test_should_create_mediafile_refrence_from_existing_diskmediafile()
    {
    }

    /**
     * @group mediafile-model
     * @group here0515
     */
    public function test_should_create_diskmediafile_and_reference_mediafile()
    {
        Storage::fake('s3');

        //$dmf = Diskmediafile::first();
        //dd('x', $dmf->toArray);
        $owner = User::firstOrFail();

        $mfname = $this->faker->slug;
        $file = UploadedFile::fake()->image($mfname, 200, 200);
        $subFolder = $owner->id;
        $s3Path = $file->store($subFolder, 's3');

        $mf = Diskmediafile::doCreate([
            'owner_id'       => $owner->id,
            'filepath'       => $s3Path,
            'mimetype'       => $file->getMimeType(),
            'orig_filename'  => $file->getClientOriginalName(),
            'orig_ext'       => $file->getClientOriginalExtension(),
            'mfname'         => $mfname,
            'mftype'         => MediafileTypeEnum::COVER,
            'resource_id'    => $owner->id,
            'resource_type'  => 'users',
        ]);

        $this->assertNotNull($mf);
//dd($mf->toArray());
        Storage::disk('s3')->assertExists($mf->filename);
        //$this->assertTrue( Storage::disk('s3')->exists($mf->filename) );

        $this->assertSame(MediafileTypeEnum::COVER, $mf->mftype);
        $this->assertSame('users', $mf->resource_type);
        $this->assertSame($owner->id, $mf->resource_id);

        //$this->assertTrue( $mediafile->has_thumb );
        //$this->assertTrue( Storage::disk('s3')->exists($mediafile->thumbFilename) );
        //$this->assertTrue( $mediafile->has_mid );
        //$this->assertTrue( Storage::disk('s3')->exists($mediafile->midFilename) );

    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();

        if ( !App::environment(['testing']) ) {
            return;
        }

        $isFollowForFree = true;
        User::factory()->count(3)->create()->each( function($u) use(&$isFollowForFree) {
            static $iter = 1;
            $avatar = null;
            $cover = null;
            $u->save();

            $timeline = $u->timeline;
            $timeline->avatar_id = $avatar->id ?? null;
            $timeline->cover_id = $cover->id ?? null;
            $timeline->is_follow_for_free = $isFollowForFree;
            $timeline->price = $isFollowForFree ? 0 : $this->faker->randomFloat(2, 1, 300);
            $timeline->save();
            $isFollowForFree = !$isFollowForFree; // toggle so we get at least one of each

            $iter++;
        });

        /*
        $this->seed(TestDatabaseSeeder::class);

        // Update or add avatars to some users for this test...
        $users = User::take(5)->get();
        $users->each( function($u) {
            $avatar = FactoryHelpers::createImage($u, MediafileTypeEnum::AVATAR, null, false); //skip S3 upload
            $u->save();
        });
         */
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

    // %NOTE: do not add to regressions as it needs to create actual images and upload to S3
    // $ APP_ENV=LOCAL  php  vendor/bin/phpunit --testdox  --group mediafile-model
//    public function test_should_create_mediafile_then_thumbnail()
//    {
//        //$post = factory(Post::class)->make();
//        $post = Post::firstOrFail();
//        $owner = $post->getPrimaryOwner();
//        $mediafile = FactoryHelpers::createImage(
//            $owner,
//            MediafileTypeEnum::POST, // string $mftype,
//            $post->id, // string $resourceID
//            true, // $doS3Upload
//            ['width'=>1280, 'height'=>720] // $attrs
//        );
//        //dd( $mediafile->filename, $mediafile->filepath, $mediafile->thumbFilename, $mediafile->thumbFilepath);
//        $mediafile->createThumbnail();
//        $mediafile->createMid();
//
//        $mediafile = Mediafile::find($mediafile->id);
//        $this->assertNotNull($mediafile);
//        $this->assertSame(MediafileTypeEnum::POST,$mediafile->mftype);
//        $this->assertTrue( Storage::disk('s3')->exists($mediafile->filename) );
//        $this->assertTrue( $mediafile->has_thumb );
//        $this->assertTrue( Storage::disk('s3')->exists($mediafile->thumbFilename) );
//        $this->assertTrue( $mediafile->has_mid );
//        $this->assertTrue( Storage::disk('s3')->exists($mediafile->midFilename) );
//
//        // Test delete
//        $mediafile->delete(); // should do soft delete
//        $this->assertTrue( $mediafile->trashed() );
//        $this->assertTrue( $mediafile->has_thumb );
//        $this->assertTrue( $mediafile->has_mid );
//        $this->assertTrue( Storage::disk('s3')->exists($mediafile->filename) );
//        $this->assertTrue( Storage::disk('s3')->exists($mediafile->thumbFilename) );
//        $this->assertTrue( Storage::disk('s3')->exists($mediafile->midFilename) );
//
//        $mediafile->deleteAssets();
//        $this->assertFalse( $mediafile->has_thumb );
//        $this->assertFalse( $mediafile->has_mid );
//        $this->assertFalse( Storage::disk('s3')->exists($mediafile->filename) );
//        $this->assertFalse( Storage::disk('s3')->exists($mediafile->thumbFilename) );
//        $this->assertFalse( Storage::disk('s3')->exists($mediafile->midFilename) );
//    }

