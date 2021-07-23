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
        // (1) get storyqueues for this user as viewer latest to oldest (whether viewed or not !)
        $daysWindow = env('STORY_WINDOW_DAYS', 10000);
        $storyqueues = Storyqueue::with('timeline', 'story')
            ->where('viewer_id', $viewer->id)
            ->where('created_at','>=',Carbon::now()->subDays($daysWindow))
            ->orderBy('created_at', 'desc')
            ->get();

        //dd($storyqueues->toArray());
        return $storyqueues;
    }

    public static function viewableTimelines(User $viewer) : Collection
    {
        $storyqueues = Storyqueue::viewableQueue($viewer);

        // filter into 2 groups:
        //   (1) at least one unviewed slide
        //   (2) all slides in timeline story viewed

        $atLeast1UnviewedSlide = collect(); // list of timelines
        $allSlidesViewed = collect(); // list of timelines
        $selected = [];
        foreach ( $storyqueues as $sq ) {
            if ( in_array($sq->timeline_id, $selected) ) {
                continue; // skip as this story timeline is already included
            }
            if ( $sq->timeline->isStoryqueueEmpty() ) {
                continue; // skip as this story timeline contains no active stories
            }
            if ($sq->timeline->isEntireStoryViewedByUser($viewer->id) ) {
                $tmpTL = $sq->timeline->makeVisible('user')->load(['user', 'avatar', 'storyqueues']);
                $tmpTL->allViewed = true;
                $allSlidesViewed->push($tmpTL);
            } else {
                $tmpTL = $sq->timeline->makeVisible('user')->load(['user', 'avatar', 'storyqueues']);
                $tmpTL->allViewed = false;
                $atLeast1UnviewedSlide->push($tmpTL);
            }
            $selected[] = $sq->timeline_id;
        }

        // Prepend my stories so my avatar is always first
        $me = Timeline::with(['avatar', 'storyqueues'])
            ->where('user_id', request()->user()->id)
            ->first();
        $me->makeVisible('user')->load(['user']);

        $timelines = ( $atLeast1UnviewedSlide->merge($allSlidesViewed) ); // ->all(); // collections of 'timelines'

        if ( !$me->isStoryqueueEmpty() ) {
            $timelines->prepend($me);
        }

        //Log::info('here ...'.json_encode($timelines->toArray(), JSON_PRETTY_PRINT)); // for debug
        return $timelines;
    }
}
