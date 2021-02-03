<?php
namespace Tests\Feature;

use DB;
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
use App\Enums\StoryTypeEnum;
use App\Enums\MediafileTypeEnum;

class StoriesTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group stories
     *  @group regression
     */
    public function test_can_store_text_story()
    {
        $user = User::first();

        $attrs = [
            'stype' => StoryTypeEnum::TEXT,
            'bgcolor' => 'blue',
            'content' => $this->faker->realText,
        ];

        $payload = [
            'attrs' => json_encode($attrs),
        ];
        $response = $this->actingAs($user)->ajaxJSON('POST', route('stories.store'), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->story);
        $storyR = $content->story;

        $this->assertSame($attrs['content'], $storyR->content);
        $this->assertSame($attrs['stype'], $storyR->stype);

        $story = Story::find($storyR->id);
        $this->assertNotNull($story);
        $this->assertSame($story->content, $storyR->content);
        $this->assertSame(StoryTypeEnum::TEXT, $storyR->stype);
    }

    /**
     *  @group stories
     *  @group regression
     *  @group this
     */
    public function test_can_store_picture_story()
    {
        Storage::fake('s3');

        $filename = 'file-foo.png';
        $user = User::first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $attrs = [
            'stype' => StoryTypeEnum::PHOTO,
            'content' => $this->faker->realText,
        ];

        $payload = [
            'attrs' => json_encode($attrs),
            'mediafile' => $file,
        ];
        $response = $this->actingAs($user)->ajaxJSON('POST', route('stories.store'), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $this->assertNotNull($content->story);
        $storyR = $content->story;

        $this->assertSame($attrs['content'], $storyR->content);
        $this->assertSame(StoryTypeEnum::PHOTO, $storyR->stype);

        $story = Story::find($storyR->id);
        $this->assertNotNull($story);
        $this->assertSame(StoryTypeEnum::PHOTO, $storyR->stype);

        // Should only be one as this is a new story
        $mediafile = Mediafile::where('resource_type', 'stories')->where('resource_id', $story->id)->first();
        $this->assertNotNull($mediafile);
        Storage::disk('s3')->assertExists($mediafile->filename);
        $this->assertSame($filename, $mediafile->mfname);
        $this->assertSame(MediafileTypeEnum::STORY, $mediafile->mftype);

        // Test relations
        $this->assertTrue( $story->mediafiles->contains($mediafile->id) );
        $this->assertEquals( $story->id, $mediafile->resource->id );
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

