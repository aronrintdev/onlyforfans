<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Enums\CfstatusTypeEnum;
use App\Models\User;
use App\Models\Contentflag;
use App\Http\Resources\ContentflagCollection;
use App\Http\Resources\Contentflag as ContentflagResource;

class ContentflagsController extends AppBaseController
{
    public function index(Request $request)
    {
        // Check permissions
        if ( !$request->user()->isAdmin() ) {
            abort(403); // %TODO: eventually non admin should be able to view own flagged?
        }

        $request->validate([
            // filters
            'cfstatus' => 'array|in:'.CfstatusTypeEnum::getKeysCsv(),
            'flaggable_type' => 'string|in:posts,timelines,users,comments,diskmediafiles,mediafiles,stories',
            'flaggable_id' => 'uuid',
            'flagger_id' => 'uuid|exists:users,id',
        ]);
        $filters = $request->only([
            'cfstatus',
            'flaggable_type',
            'flaggable_id',
            'flagger_id',
        ]) ?? [];

        $sessionUser = $request->user();

        $query = Contentflag::query(); // Init query

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
                case 'cfstatus':
                case 'flaggable_id':
                case 'flaggable_type':
                case 'flagger_id':
                    $query->where($key, $v);
                    break;
                default:
            }
        }

        switch ($request->sortBy) {
        case 'cfstatus':
        case 'flaggable_type':
        case 'created_at':
        case 'updated_at':
            $sortDir = $request->sortDir==='asc' ? 'asc' : 'desc';
            $query->orderBy($request->sortBy, $sortDir);
            break;
        default:
            $query->orderBy('updated_at', 'desc');
        }


        $data = $query->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new ContentflagCollection($data);
    }

    public function store(Request $request)
    {
        $this->authorize('store', Contentflag::class);

        $request->validate([
            //'cfstatus' => 'required|in:'.CfstatusTypeEnum::getKeysCsv(),
            'flaggable_type' => 'string|in:posts,timelines,users,comments,diskmediafiles,mediafiles,stories',
            'flaggable_id' => 'uuid',
            'cattrs'     => 'nullable|json',
            'meta'       => 'nullable|json',
        ]);

        $obj = Contentflag::create(array_merge(
            [ 
                'flagger_id' => $request->user()->getKey(),
                'cfstatus' => CfstatusTypeEnum::PENDING,
            ],
            $request->all(),
        ));

        return new ContentflagResource($obj);
    }

    public function update(Request $request, Contentflag $obj)
    {
        $this->authorize('update', $obj);

        $request->validate([
            'alias'  => 'nullable|string|max:255',
            'cattrs' => 'nullable|json',
            'meta'   => 'nullable|json',
        ]);

        $fields = ['alias', 'cattrs', 'meta'];

        foreach($fields as $field) {
            if ($request->has($field)) {
                $obj->{$field} = $request->input($field);
            }
        }

        $obj->save();

        return new ContentflagResource($obj);
    }

    public function show(Contentflag $obj)
    {
        $this->authorize('view', $obj);
        return new ContentflagResource($obj);
    }

    public function destroy(Contentflag $obj)
    {
        $this->authorize('delete', $obj);
        $obj->delete();
        return;
    }

}
