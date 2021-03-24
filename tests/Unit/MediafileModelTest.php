<?php
namespace Tests\Unit;

use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
//use App\Models\Image;

class MediafileModelTest extends TestCase
{
    use WithFaker; 
    //use RefreshDatabase; -- need to impl locally to disable during run-time %TODO

    /**
     * @group mediafile-model
     */
    public function test_should_upload_to_s3()
    {
        //$post = factory(Post::class)->make();
        $post = Post::firstOrFail();

        $file = UploadedFile::fake()->image('file-foo.png', 400, 400);

        $mediafile = Mediafile::create([
            'resource_id'=>$post->id,
            'resource_type'=>'posts',
            'mfname' => $this->faker->slug,
            'filename' => $this->faker->slug,
            'mftype' => MediafileTypeEnum::POST,
            'mimetype' => $file->getMimeType(),
            'orig_filename' => $file->getClientOriginalName(),
            'orig_ext' => $file->getClientOriginalExtension(),
        ]);

        // Test it exists
        $mediafile = Mediafile::find($mediafile->id);
        $this->assertNotNull($mediafile);
        //$this->assertFileExists($mediafile->absolute_resource_path);
        //$this->assertSame('employees',$mediafile->resource_type);
        //$this->assertSame($employee->id,$mediafile->resource_id);
        $this->assertSame(MediafileTypeEnum::POST,$mediafile->mftype);
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
