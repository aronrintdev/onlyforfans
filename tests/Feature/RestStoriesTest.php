<?php
namespace Tests\Feature;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\Diskmediafile;
use App\Models\Mediafile;
use App\Models\Story;
use App\Models\Timeline;
use App\Models\User;
use App\Enums\StoryTypeEnum;
use App\Enums\MediafileTypeEnum;

class RestStoriesTest extends TestCase
{
    use WithFaker;

    /**
     *  @group stories
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_my_stories()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;

        $payload = [
            'timeline_id' => $timeline->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON('GET', route('stories.index'), $payload);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $storiesR = $content->data;
        $this->assertGreaterThan(0, count($storiesR));

        $nonTimelineStories = collect($storiesR)->filter( function($s) use(&$timeline) {
            return $s->timeline_id !== $timeline->id;
        });
        $this->assertEquals(0, $nonTimelineStories->count(), 'Returned a story not on specified timeline');
    }

    /**
     *  @group stories
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_stories_filtered_by_timeline()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();

        $payload = [
            'timeline_id' => $timeline->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('stories.index'), $payload);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $storiesR = $content->data;
        $this->assertGreaterThan(0, count($storiesR));

        $nonTimelineStories = collect($storiesR)->filter( function($s) use(&$timeline) {
            return $s->timeline_id !== $timeline->id;
        });
        $this->assertEquals(0, $nonTimelineStories->count(), 'Returned a story not on specified timeline');
    }

    /**
     *  @group stories
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_stories_filtered_by_stype()
    {
        // %NOTE - seeder only has TEXT stories at this time (?)
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();

        $payload = [
            'timeline_id' => $timeline->id,
            'stypes' => [StoryTypeEnum::PHOTO, StoryTypeEnum::TEXT],
        ];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('stories.index'), $payload);
        $content = json_decode($response->content());
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $storiesR = $content->data;
        $this->assertGreaterThan(0, count($storiesR));

        $nonTimelineStories = collect($storiesR)->filter( function($s) use(&$timeline) {
            return $s->timeline_id !== $timeline->id;
        });
        $this->assertEquals(0, $nonTimelineStories->count(), 'Returned a story not on specified timeline');

        $nonFilteredStories = collect($storiesR)->filter( function($s) use(&$timeline) {
            return $s->stype !== StoryTypeEnum::PHOTO && $s->stype !== StoryTypeEnum::TEXT;
        });
        $this->assertEquals(0, $nonFilteredStories->count(), 'Returned a story not in specified filter');
    }

    /**
     *  @group stories
     *  @group regression
     *  @group regression-base
     */
    public function test_can_not_list_stories_on_unfollowed_timeline()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $nonFan = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->first();

        $payload = [
            'timeline_id' => $timeline->id,
        ];
        $response = $this->actingAs($nonFan)->ajaxJSON('GET', route('stories.index'), $payload);
        $response->assertStatus(403);
    }

    /**
     *  @group stories
     *  @group regression
     *  @group regression-base
     *  @group june0625
     */
    public function test_can_list_grouped_stories_on_followed_timelines()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();

        $payload = [ ];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('timelines.myFollowedStories'), $payload);
        $content = json_decode($response->content());
        $response->assertStatus(200);
        //dd($content);
        $response->assertJsonStructure([
            'data' => [ 
                0 => [
                    'id',
                    'slug',
                    'name',
                    'price',
                    'avatar',
                    'is_follow_for_free',
                    'stories',
                    //'stories' => [
                        //'mediafiles',
                    //],
                    'is_owner',
                    'is_following',
                    'is_subscribed',
                    'created_at',
                ],
            ],
        ]);
        /*
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $storiesR = $content->data;
        $this->assertGreaterThan(0, count($storiesR));

        $storiesOnTimelineNotFollowedByFan = collect($storiesR)->filter( function($s) use(&$fan) {
            $story = Story::find($s->id);
            return !$story->timeline->followers->contains($fan->id);
        });
        $this->assertEquals(0, $storiesOnTimelineNotFollowedByFan->count(), 'Returned a story on a timeline not followed by fan');
         */
    }

    /**
     *  @group stories
     *  @group regression
     *  @group regression-base
     */
    // %NOTE: %PSG 20210625: this is actually not what we want for the story bar...as ideally we
    // want the stories returned with some kind of timeline grouping structure (?)
    public function test_can_list_stories_on_followed_timelines()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->firstOrFail();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();

        $payload = [
            'following' => true,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('GET', route('stories.index'), $payload);
        $content = json_decode($response->content());
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $storiesR = $content->data;
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
     *  @group regression-base
     *  @group july08
     */
    public function test_can_store_text_story()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $owner = $timeline->user;

        $stype = StoryTypeEnum::TEXT;
        $bgcolor = 'blue';
        $content = $this->faker->realText;
        $link = 'google.com';

        $payload = [
            'stype' => $stype,
            'bgcolor' => $bgcolor,
            'content' => $content,
            'link' => $link,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('stories.store'), $payload);

        $response->assertStatus(201);

        $responseContent = json_decode($response->content());
        $this->assertNotNull($responseContent->story);
        $storyR = $responseContent->story;

        $this->assertSame($content, $storyR->content);
        $this->assertSame($stype, $storyR->stype);
        $this->assertSame($link, $storyR->swipe_up_link);

        $story = Story::find($storyR->id);
        $this->assertNotNull($story);
        $this->assertSame($story->content, $storyR->content);
        $this->assertSame(StoryTypeEnum::TEXT, $storyR->stype);

        $this->assertNotNull($story->storyqueues);
        $this->assertEquals($story->timeline->followers->count(), $story->storyqueues->count());
    }

    /**
     *  @group stories
     *  @group regression
     *  @group regression-base
     *  @group july08
     */
    public function test_can_store_picture_story()
    {
        Storage::fake('s3');

        $filename = $this->faker->slug.'.png';
        $owner = User::first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $stype = StoryTypeEnum::PHOTO;
        $content = $this->faker->realText;

        $payload = [
            'stype' => $stype,
            'content' => $content,
            'mediafile' => $file,
        ];
        $response = $this->actingAs($owner)->ajaxJSON('POST', route('stories.store'), $payload);
        $response->assertStatus(201);

        $responseContent = json_decode($response->content());
        $this->assertNotNull($responseContent->story);
        $storyR = $responseContent->story;

        $this->assertSame($content, $storyR->content);
        $this->assertSame(StoryTypeEnum::PHOTO, $storyR->stype);

        $story = Story::with('mediafiles')->find($storyR->id);
        $this->assertNotNull($story);
        $this->assertSame(StoryTypeEnum::PHOTO, $storyR->stype);
        $this->assertEquals($owner->id, $story->timeline->user->id);

        // Should only be one as this is a new story
        $mf = Mediafile::where('resource_type', 'stories')->where('resource_id', $story->id)->first();
        $this->assertNotNull($mf);
        $this->assertSame($filename, $mf->mfname);
        $this->assertSame(MediafileTypeEnum::STORY, $mf->mftype);
        Storage::disk('s3')->assertExists($mf->diskmediafile->filepath);
        $this->assertSame($owner->id, $mf->diskmediafile->owner_id);

        // Test relations
        $this->assertTrue( $story->mediafiles->contains($mf->id) );
        $this->assertEquals( $story->id, $mf->resource->id );

        $this->assertNotNull($story->storyqueues);
        $this->assertEquals($story->timeline->followers->count(), $story->storyqueues->count());
    }

    /**
     *  @group stories
     *  @group regression
     *  @group regression-base
     */
    public function test_owner_can_delete_picture_story()
    {
        Storage::fake('s3');

        $filename = $this->faker->slug.'.png';
        $owner = User::first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $payload = [
            'stype' => StoryTypeEnum::PHOTO,
            'content' => $this->faker->realText,
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
     *  @group regression-base
     */
    public function test_nonowner_can_not_delete_picture_story()
    {
        Storage::fake('s3');

        $filename = $this->faker->slug;
        $owner = User::first();
        $nonowner = User::where('id', '<>', $owner->id)->first();
        $file = UploadedFile::fake()->image($filename, 200, 200);

        $payload = [
            'stype' => StoryTypeEnum::PHOTO,
            'content' => $this->faker->realText,
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
     *  @group regression-base
     */
    public function test_can_like_then_unlike_viewable_story()
    {
        $timeline = Timeline::has('stories', '>=', 1)->has('followers', '>=', 1)->first();
        $creator = $timeline->user;
        $fan = $timeline->followers[0];
        $story = $timeline->stories[0];

        // remove any existing likes by fan...
        DB::table('likeables')
            ->where('liker_id', $fan->id)
            ->where('likeable_type', 'stories')
            ->where('likeable_id', $story->id)
            ->delete();

        // LIKE the story
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('likeables.update', $fan->id), [ 
            'likeable_type' => 'stories',
            'likeable_id' => $story->id,
        ]);
        $response->assertStatus(200);
    }

    /**
     *  @group stories
     *  @group regression
     *  @group regression-base
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
        //$this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

