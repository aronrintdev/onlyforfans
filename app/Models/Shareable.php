<?php 
namespace App\Models;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class Shareable extends Model 
{
    protected $guarded = [ 'created_at', 'updated_at' ];

    protected $append = [
        'notes',
    ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------

    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            switch ($model->shareable_type) {
            case 'timelines':
                // create [storyqueuelogs] records for the new follower (sharee)
                //$stories = $model->shareable->stories;
                $daysWindow = Config::get('stories.window_days', 1);
                $stories = Story::where('timeline_id', $model->shareable_id)
                    ->where('created_at', '>=', Carbon::now()->subDays($daysWindow))
                    ->get(); // get all stories for the shared timeline created in the last day or equiv
                $attrs = [];
                $stories->each( function($s) use(&$model, &$attrs) {
                    $attrs[] = [
                        'viewer_id' => $model->sharee_id,
                        'story_id' => $s->id,
                        'timeline_id' => $s->timeline->id,
                        'created_at' => $s->created_at, // %NOTE: use the created date of the *story* here (!)
                        'updated_at' => $s->updated_at,
                    ];
                });
                DB::table('storyqueues')->insert($attrs);
                break;
            }
        });

        static::deleting(function ($model) {
            switch ($model->shareable_type) {
            case 'timelines':
                // Delete any storyqueue relations (for this follower only!)...
                $storyqueues = Storyqueue::where('viewer_id', $model->sharee_id)
                    ->where('timeline_id', $model->shareable_id)
                    ->get();
                foreach ($storyqueues as $sq) {
                    $sq->delete();
                }
                break;
            }
        });
    }

    //--------------------------------------------
    // %%% Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [
        'cattrs'    => 'array',
        'meta'      => 'array',
    ];

    //--------------------------------------------
    // %%% Relationships
    //--------------------------------------------

    public function shareable() {
        return $this->morphTo();
    }

    public function sharee() { 
        return $this->belongsTo(User::class, 'sharee_id');
    }

    //--------------------------------------------
    // %%% Scopes
    //--------------------------------------------

    /*
    public function scopeSort($query, $sortBy, $sortDir='desc')
    {
        $sortDir = $sortDir==='asc' ? 'asc' : 'desc';
        switch ($sortBy) {
        case 'slug':
            $query->orderBy('shareable.slug', $sortDir);
            break;
        case 'created_at':
            $query->orderBy($sortBy, $sortDir);
            break;
        default:
            $query->latest();
        }
        return $query;
    }
     */

    public function getNotesAttribute($value) {
        return Notes::where('user_id', $this->shareable->user_id)
            ->where('notes_id', $this->sharee->timeline->id)
            ->first();
    }

}

