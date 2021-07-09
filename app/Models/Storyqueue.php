<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

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
    public static function viewableQueue(User $viewer) : Collection
    {
        $storyqueues = Storyqueue::with('timeline')->select(DB::raw('sq.*'))
            ->from(DB::raw('(SELECT * FROM storyqueues ORDER BY created_at DESC) sq'))
            ->where('viewer_id', $viewer->id)
            ->whereNull('viewed_at')
            ->withTrashed()->whereNull('sq.deleted_at') // won't work without this (??)
            ->groupBy('sq.timeline_id')
            ->get();
        return $storyqueues;
    }

    public static function viewableTimelines(User $viewer) : Collection
    {
        $storyqueues = Storyqueue::viewableQueue($viewer);
        $timelines = $storyqueues->map( function($sq) {
            return $sq->timeline;
        });
        return $timelines;
    }
}
