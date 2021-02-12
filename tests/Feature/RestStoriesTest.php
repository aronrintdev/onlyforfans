<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\Mediafile;
use App\Models\Story;
use App\Models\Timeline;
use App\Models\User;
use App\Enums\StoryTypeEnum;
use App\Enums\MediafileTypeEnum;

class StoriesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group stories
     *  @group regression
     */
    public function test_can_index_my_stories()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        $payload = [
            'filters' => [
                'timeline_id' => $timeline->id,
            ],
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('stories.index'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->stories);
        $storiesR = $content->stories;
        $this->assertGreaterThan(0, count($storiesR));

        $nonTimelineStories = collect($storiesR)->filter( function($s) use(&$timeline) {
            return $s->timeline_id !== $timeline->id;
        });
        $this->assertEquals(0, $nonTimelineStories->count(), 'Returned a story not on specified timeline');
    }

    /**
     *  @group stories
     *  @group regression
     */
    public function test_can_index_stories_on_followed_timeline()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();

        $payload = [
            'filters' => [
                'timeline_id' => $timeline->id,
            ],
        ];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('stories.index'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->stories);
        $storiesR = $content->stories;
        $this->assertGreaterThan(0, count($storiesR));

        $nonTimelineStories = collect($storiesR)->filter( function($s) use(&$timeline) {
            return $s->timeline_id !== $timeline->id;
        });
        $this->assertEquals(0, $nonTimelineStories->count(), 'Returned a story not on specified timeline');
    }

    /**
     *  @group stories
     *  @group regression
     */
    public function test_can_not_index_stories_on_unfollowed_timeline()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $nonFan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [
            'filters' => [
                'timeline_id' => $timeline->id,
            ],
        ];
        $response = $this->actingAs($nonFan)->ajaxJSON('GET', route('stories.index'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group stories
     *  @group regression
     */
    public function test_can_index_stories_of_followed_timelines()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();

        $payload = [
            'filters' => [
                'following' => true,
            ],
        ];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('stories.index'), $payload);
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertNotNull($content->stories);
        $storiesR = $content->stories;
        $this->assertGreaterThan(0, count($storiesR));

        $storiesOnTimelineNotFollowedByFan = collect($storiesR)->filter( function($s) use(&$fan) {
            $story = Story::find($s->id);
            return !$story->timeline->followers->contains($fan->id);
        });
        $this->assertEquals(0, $storiesOnTimelineNotFollowedByFan->count(), 'Returned a story on a timeline not followed by fan');
    }


    /**
     *  @group stories
     *  @group regression
     */
    public function test_can_store_text_story()
    {
        $owner = User::first();

        $attrs = [
            'stype' => StoryTypeEnum::TEXT,
            'bgcolor' => 'blue',
            'content' => $this->faker->realText,
        ];

        $payload = [
            'attrs' => json_encode($attrs),
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('stories.store'), $payload);

        $response->assertStatus(201);

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
     */
    public function test_can_store_picture_story()
    {
        Storage::fake('s3');

        $filename = $this->faker->slug;
        $owner = User::first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $attrs = [
            'stype' => StoryTypeEnum::PHOTO,
            'content' => $this->faker->realText,
        ];

        $payload = [
            'attrs' => json_encode($attrs),
            'mediafile' => $file,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->story);
        $storyR = $content->story;

        $this->assertSame($attrs['content'], $storyR->content);
        $this->assertSame(StoryTypeEnum::PHOTO, $storyR->stype);

        $story = Story::find($storyR->id);
        $this->assertNotNull($story);
        $this->assertSame(StoryTypeEnum::PHOTO, $storyR->stype);
        $this->assertEquals($owner->id, $story->timeline->user->id);

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

    /**
     *  @group stories
     *  @group regression
     */
    public function test_owner_can_delete_picture_story()
    {
        Storage::fake('s3');

        $filename = $this->faker->slug;
        $owner = User::first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $attrs = [
            'stype' => StoryTypeEnum::PHOTO,
            'content' => $this->faker->realText,
        ];

        $payload = [
            'attrs' => json_encode($attrs),
            'mediafile' => $file,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->story);
        $storyR = $content->story;
        $story = Story::find($storyR->id);
        $mediafile = Mediafile::where('resource_type', 'stories')->where('resource_id', $story->id)->first();
        $this->assertNotNull($mediafile);

        $response = $this->actingAs($owner)->ajaxJSON('DELETE', route('stories.destroy', $storyR->id));
        $response->assertStatus(200);
        Storage::disk('s3')->assertMissing($mediafile->filename);
        $exists = Mediafile::where('resource_type', 'stories')->where('resource_id', $story->id)->count();
        $this->assertEquals(0, $exists);
    }

    /**
     *  @group stories
     *  @group regression
     */
    public function test_nonowner_can_not_delete_picture_story()
    {
        Storage::fake('s3');

        $filename = $this->faker->slug;
        $owner = User::first();
        $nonowner = User::where('id', '<>', $owner->id)->first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $attrs = [
            'stype' => StoryTypeEnum::PHOTO,
            'content' => $this->faker->realText,
        ];

        $payload = [
            'attrs' => json_encode($attrs),
            'mediafile' => $file,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(201);

        $content = json_decode($response->content());
        $this->assertNotNull($content->story);
        $storyR = $content->story;

        $response = $this->actingAs($nonowner)->ajaxJSON('DELETE', route('stories.destroy', $storyR->id));
        $response->assertStatus(403);
    }

    /**
     *  @group stories
     *  @group regression
     */
    public function test_can_like_then_unlike_viewable_story()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $story = $timeline->stories[0];

        // remove any existing likes by fan...
        DB::table('likeables')
            ->where('likee_id', $fan->id)
            ->where('likeable_type', 'stories')
            ->where('likeable_id', $story->id)
            ->delete();

        // LIKE the story
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('likeables.update', $fan->id), [ // fan->likee
            'likeable_type' => 'stories',
            'likeable_id' => $story->id,
        ]);
        $response->assertStatus(200);
    }

    /**
     *  @group stories
     *  @group regression
     */
    public function test_can_not_like_unviewable_story()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $story = $timeline->stories[0];

        $nonFan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $response = $this->actingAs($nonFan)->ajaxJSON('PUT', route('likeables.update', $nonFan->id), [
            'likeable_type' => 'stories',
            'likeable_id' => $story->id,
        ]);
        $response->assertStatus(403);
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

