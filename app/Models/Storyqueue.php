<?php
namespace App\Models;

use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Storyqueue extends Model
{
    use SoftDeletes;

    protected $guarded = [ 'id', 'created_at', 'updated_at', ];

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function timeline() {
        return $this->belongsTo(Timeline::class);
    }
    public function story() {
        return $this->belongsTo(Story::class);
    }
    public function viewer() {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    //--------------------------------------------
    // Other Methods
    //--------------------------------------------

    // https://stackoverflow.com/questions/43282161/get-most-recent-row-with-group-by-and-laravel
    // %FIXME: rename
    public static function viewableQueue(User $viewer) : Collection
    {
        /*
        $storyqueues = Storyqueue::with('timeline', 'story')->select(DB::raw('sq.*'))
            ->from(DB::raw('(SELECT * FROM storyqueues where viewer_id="'.$viewer->id.'" ORDER BY created_at DESC) sq')) // %FIXME: sql injection defense!
            ->where('viewer_id', $viewer->id)
            ->whereNull('viewed_at')
            ->withTrashed()->whereNull('sq.deleted_at') // won't work without this (??)
            ->groupBy('sq.timeline_id')
            ->orderBy('sq.created_at', 'desc')
            //->orderBy('sq.created_at', 'asc')
            ->get();
        //dd($storyqueues->toArray());
        return $storyqueues;
         */

        // (1) get storyqueues for this user as viewer latest to oldest (whether viewed or not !)
        $daysWindow = env('STORY_WINDOW_DAYS', 10000);
        $storyqueues = Storyqueue::with('timeline', 'story')
            ->where('viewer_id', $viewer->id)
            ->where('created_at','>=',Carbon::now()->subDays($daysWindow))
            //->whereNull('viewed_at')
            ->orderBy('created_at', 'desc')
            ->get();

        /*
        $storyqueues = $storyqueues->sortByDesc( function($sq) use(&$viewer) {

            $stories = Storyqueue::select(['id','created_at'])
                ->where('timeline_id', $this->id)
                //->where('viewer_id', $viewer->id)
                ->orderBy('created_at', 'desc')->get();
            return ($stories->count()>0) ? $stories[0] : null;

            //return $sq->timeline->getLatestStory($viewer)->created_at; // sort by latest story slide in the timeline
        });
         */
        //dd($storyqueues->toArray());
        return $storyqueues;
    }

    public static function viewableTimelines(User $viewer) : Collection
    {
        $storyqueues = Storyqueue::viewableQueue($viewer);

        // filter into 2 groups:
        //   (1) at least one unviewed slide
        //   (2) all slides in timeline story viewed

        $atLeast1UnviewedSlide = collect();
        $allSlidesViewed = collect();
        $selected = [];
        foreach ( $storyqueues as $sq ) {
            if ( in_array($sq->timeline_id, $selected) ) {
                continue;
            }
            if ($sq->timeline->isEntireStoryViewedByUser($viewer) ) {
                $tmp = $sq->timeline->makeVisible('user')->load(['user', 'avatar', 'storyqueues']);
                $tmp->allViewed = true;
                $allSlidesViewed->push($tmp);
                //$allSlidesViewed->push($sq->timeline->makeVisible('user')->load(['user', 'avatar', 'storyqueues']));
            } else {
                $tmp = $sq->timeline->makeVisible('user')->load(['user', 'avatar', 'storyqueues']);
                $tmp->allViewed = false;
                $atLeast1UnviewedSlide->push($tmp);
                //$atLeast1UnviewedSlide->push($sq->timeline->makeVisible('user')->load(['user', 'avatar', 'storyqueues']));
            }
            $selected[] = $sq->timeline_id;
        }

        // Prepend my stories so my avatar is always first
        $me = Timeline::with(['avatar', 'storyqueues'])
            ->where('user_id', request()->user()->id)
            ->first();
        $me->makeVisible('user')->load(['user']);

        $timelines = ( $atLeast1UnviewedSlide->merge($allSlidesViewed) ); // ->all(); // collections of 'timelines'
        $timelines->prepend($me);
        //$timelines = $atLeast1UnviewedSlide;
        /*
        $timelines = $storyqueues->reduce( function($acc, $sq) {
            static $selected = [];
            if ( !in_array($sq->timeline_id, $selected) ) {
                $selected[] = $sq->timeline_id;
                $acc->push($sq->timeline->makeVisible('user')->load(['user', 'avatar', 'storyqueues']));
            }
            return $acc;
        }, collect());
         */

        /*
        $timelines = $storyqueues->map( function($sq) {
            static $selected = [];
            if ( !in_array($sq->timeline_id, $selected) ) {
                $selected[] = $sq->timeline_id;
                return $sq->timeline;
            } else {
                return false;
            }
        });
         */
        /*
        $timelines = $storyqueues->map( function($sq) use(&$viewer) {
            $tl =  $sq->timeline->makeVisible(['user'])->load([
                'avatar', 
                //'stories', 
                'user',
                'storyqueues' => function($q1) use(&$viewer) {
                    $q1->where('viewer_id', $viewer->id);
                },
            ]); // %TODO: cleanup, don't need user (just user_id)
            return $tl;
        });
         */
        Log::info('here ...'.json_encode($timelines->toArray(), JSON_PRETTY_PRINT));
        return $timelines;
    }
}
