<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Tests\TestCase;
use Ramsey\Uuid\Uuid;

use App\Models\Timeline;
use App\Models\Mediafile;
use App\Models\Story;
use App\Enums\MediafileTypeEnum;

class StoryModelTest extends TestCase
{
    /**
     * @group story-model
     * @group OFF-july07
     */
    public function test_latest_story_by_timeline()
    {
        $timeline = Timeline::has('stories','>',0)->has('followers','>',0)->first();
        $latest = $timeline->getLatestStory();
        dd($latest->toArray());
    }

    /**
     * @group story-model
     * @group july07
     */
    public function test_story_sort()
    {
        $timeline = Timeline::has('stories','>',0)->has('followers','>',0)->first();
        $creator = $timeline->user;
        $fan = $timeline->followers->first();

        $query = Timeline::select(['id','slug','created_at'])->has('stories');
        $query->with('stories:id,slug,timeline_id,created_at');
        //$query->with(['stories.mediafiles']);
        $query->whereIn('id', $fan->followedtimelines->pluck('id'));
        $timelines = $query->orderBy('slug')->get();

        $print = [];
        $timelines->each( function($t) use(&$print) {
            $latest = $t->getLatestStory();
            $print[] = [
                'timeline_pkid' => $t->id,
                'timeline_slug' => $t->slug,
                'timeline_created_at' => $t->created_at->toDateTimeString(),
                'timeline_latest_story_id' => $latest->id,
                'timeline_latest_story_slug' => $latest->slug,
                'timeline_latest_story_created_at' => $latest->created_at->toDateTimeString(),
            ];
        });

        dump('----- BEFORE -----', $print);

        $timelines = $timelines->sortByDesc( function($t) {
            return $t->getLatestStory()->created_at;
        });

        $print = [];
        $timelines->each( function($t) use(&$print) {
            $latest = $t->getLatestStory();
            $print[] = [
                'timeline_pkid' => $t->id,
                'timeline_slug' => $t->slug,
                'timeline_created_at' => $t->created_at->toDateTimeString(),
                'timeline_latest_story_id' => $latest->id,
                'timeline_latest_story_slug' => $latest->slug,
                'timeline_latest_story_created_at' => $latest->created_at->toDateTimeString(),
            ];
        });

        dump('----- AFTER -----', $print);
        //dd('2', $timelines->toArray());

        /*
        $timelines = $query->get()->sortByDesc( function($v, $k) {
            $stories = $v->stories->sortByDesc('created_at');
            return $stories[0]->created_at;
        });
        */
    }

    /**
     * @group story-model
     */
    public function test_debug()
    {
        $mediafile = Mediafile::find(4);
        //$f = $s->mediafiles->first()->filename;
        $f = $mediafile->filename;
        //$s = Storage::disk('s3')->get($f);
        $s = Storage::disk('s3')->url($f);
        //$s = Storage::disk('s3')->get($s->mediafiles->first()->filename);
    }


    /**
     * @group OFF-mfdev
     */
    public function test_can_create_photo_story()
    {
        $story = factory(Story::class)->create();
        $story->mediafiles()->save(factory(Mediafile::class)->create([
            'resource_type' => 'stories',
            'mftype' => MediafileTypeEnum::STORY,
        ]));
        $story->refresh();
        $this->assertNotNull($story);
        $this->assertNotNull($story->id);
        $this->assertNotNull($story->mediafiles);
        $this->assertNotNull($story->mediafiles->first());
    }

}
