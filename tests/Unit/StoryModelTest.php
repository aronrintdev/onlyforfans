<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

use App\Models\Mediafile;
use App\Models\Story;
use App\Models\Storyqueue;
use App\Models\Timeline;
use App\Models\User;
use App\Enums\MediafileTypeEnum;

class StoryModelTest extends TestCase
{
    /**
     * @group OFF-story-model
     */
    public function test_storyqueue_exists_for_owner()
    {
    }

    /**
     * @group story-model
     */
    public function test_basic_ts_integrity()
    {
        $stories = Story::get();
        $stories->each( function($s) {
            $s->storyqueues->each( function($sq) use(&$s) {
                $this->assertEquals($s->timeline->id, $sq->timeline->id);
                $this->assertEquals($s->created_at, $sq->created_at);
            });
        });
    }

    /**
     * @group story-model
     * @group july09
     */
    public function test_viewable_timelines()
    {
        $daysWindow = env('STORY_WINDOW_DAYS', 10000);
        $dummyTimeline = Timeline::has('storyqueues','>=',1)->has('followers','>=',1)->first();
        $creator = $dummyTimeline->user;
        $fan = $dummyTimeline->followers->first();
        unset($dummyTimeline); // only used to get a meaningful creator and fan

        //--

        $timelines = Storyqueue::viewableTimelines($fan);
        //dd('storyqueues', $storyqueues->toArray());

        $seenTimelines = collect();
        $timelines->each( function($t) use($daysWindow, &$fan, &$seenTimelines) {
            static $lastTimeline = null;

            $this->assertFalse( $seenTimelines->contains($t->id) ); // ensure unique list
            $seenTimelines->push($t->id);

            if ( !empty($lastTimeline) ) {
                $thisTlLatestStory = $t->storyqueues()->orderBy('created_at', 'desc')->first();
                $lastTlLatestStory = $lastTimeline->storyqueues()->orderBy('created_at', 'desc')->first();
                //$thisTlLatestStory = $t->stories()->orderBy('created_at', 'desc')->first();
                //$lastTlLatestStory = $lastTimeline->stories()->orderBy('created_at', 'desc')->first();
                $this->assertLessThan($lastTlLatestStory->created_at->timestamp, $thisTlLatestStory->created_at->timestamp);
            }
            $lastTimeline = $t;

        });
    }

    /**
     * @group story-model
     * @group july09
     */
    public function test_viewable_queue()
    {
        $daysWindow = env('STORY_WINDOW_DAYS', 10000);
        $dummyTimeline = Timeline::has('storyqueues','>=',1)->has('followers','>=',1)->first();
        $creator = $dummyTimeline->user;
        $fan = $dummyTimeline->followers->first();
        unset($dummyTimeline); // only used to get a meaningful creator and fan

        //--

        //dd($storyqueues->toArray());

        $storyqueues = Storyqueue::viewableQueue($fan);
        /*
        $storyqueues = Storyqueue::with('timeline')->select(DB::raw('sq.*'))
            ->from(DB::raw('(SELECT * FROM storyqueues ORDER BY created_at DESC) sq'))
            ->where('viewer_id', $fan->id)
            ->whereNull('viewed_at')
            ->withTrashed()->whereNull('sq.deleted_at') // won't work without this (??)
            ->groupBy('sq.timeline_id')
            ->get();
            //->pluck('timeline');
         */
        //dd('storyqueues', $storyqueues->toArray());

        $seenTimelines = collect();
        $storyqueues->each( function($sq) use($daysWindow, &$fan, &$seenTimelines) {
            //$this->assertFalse( $seenTimelines->contains($sq->timeline_id) ); // ensure unique list
            //$seenTimelines->push($sq->timeline_id);
            //static $lastTimeline = null;

            // Get the lastest storyqueue on the iter'd sq's timeline, verify it's the same as returned
            // by the group-by above
            $latestSQ = Storyqueue::where('viewer_id', $fan->id)
                ->where('timeline_id', $sq->timeline_id)
                ->whereNull('viewed_at')
                ->where('created_at', '>=', Carbon::now()->subDays($daysWindow))
                ->orderBy('created_at', 'desc')
                ->first();

            //$this->assertEquals($latestSQ->id, $sq->id);

            $t = $sq->timeline;
            if ( !empty($lastTimeline) ) {
                $thisTlLatestStory = $t->storyqueues()->orderBy('created_at', 'desc')->first();
                $lastTlLatestStory = $lastTimeline->storyqueues()->orderBy('created_at', 'desc')->first();
                //$thisTlLatestStory = $t->stories()->orderBy('created_at', 'desc')->first();
                //$lastTlLatestStory = $lastTimeline->stories()->orderBy('created_at', 'desc')->first();
                $this->assertLessThan($lastTlLatestStory->created_at->timestamp, $thisTlLatestStory->created_at->timestamp);
            }
            $lastTimeline = $t;
            /*
            if ( $latestSQ->id !== $sq->id ) {
                $list = Storyqueue::where('viewer_id', $fan->id)
                    ->where('timeline_id', $sq->timeline_id)
                    ->whereNull('viewed_at')
                    ->where('created_at', '>=', Carbon::now()->subDays($daysWindow))
                    ->orderBy('created_at', 'desc')
                    ->get();
                dd('list', $list->toArray(), 'latest', $latestSQ->toArray(), 'sq', $sq->toArray());
                //dd($latestSQ->toArray(), $sq->toArray());
            }
             */
        });
    }

    /**
     * @group OFF-story-model
     */
    public function test_latest_story_by_timeline()
    {
        $timeline = Timeline::has('stories','>',0)->has('followers','>',0)->first();
        $latest = $timeline->getLatestStory();
        dd($latest->toArray());
    }

    /**
     * @group OFF-story-model
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
