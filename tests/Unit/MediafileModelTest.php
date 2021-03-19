<?php
namespace Tests\Unit;

use DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\Story;
use Ramsey\Uuid\Uuid;
use App\Models\Mediafile;
use App\Enums\MediafileTypeEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
//use App\Models\Image;

class MediafileModelTest extends TestCase
{
    /**
     * @group OFF-mfdev
     */
    public function test_hello_story()
    {
        $story = factory(Story::class)->make();
        $story->refresh();
        $this->assertNotNull($story);
        $this->assertNotNull($story->timeline);
        $this->assertGreaterThan(0, $story->timeline->id);
        $this->assertNotNull($story->timeline->user);
        $this->assertGreaterThan(0, $story->timeline->user->id);

        //$car = Car::find($result->content());
        //$this->assertNotNull($car);
        //$this->assertEquals($data, $car->only(['make']));
    }

    /**
     * @group OFF-mfdev
     */
    public function test_hello_user()
    {
        $user = factory(User::class)->make();
        $user->refresh();
        $this->assertNotNull($user);
        $this->assertNotNull($user->timeline);
        $this->assertGreaterThan(0, $user->timeline->id);

        //$car = Car::find($result->content());
        //$this->assertNotNull($car);
        //$this->assertEquals($data, $car->only(['make']));
    }

    /**
     * @group OFF-mfdev
     */
    public function test_should_upload_to_s3()
    {
        $story = factory(Story::class)->make();

        $file = UploadedFile::fake()->image('file-foo.png', 400, 400);

        $mediafile = Mediafile::create([
            'resource_id'=>$story->id,
            'resource_type'=>'stories',
            'filename'=>(string) Uuid::uuid4(),
            'mftype' => MediafileTypeEnum::STORY,
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
        $this->assertSame(MediafileTypeEnum::STORY,$mediafile->mftype);
    }

}
