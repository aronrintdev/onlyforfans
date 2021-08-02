<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Models\User;
use App\Models\Contentflag;
use App\Http\Resources\ContactflagCollection;
use App\Http\Resources\Contactflag as ContactflagResource;

class ContentflagsController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            //'owner_id'                => 'uuid|exists:users,id',
        ]);
        $filters = $request->only([
            //'owner_id',
        ]) ?? [];

        $sessionUser = $request->user();

        $query = Contentflag::query(); // Init query

        // Check permissions
        if ( !($request->user()->isAdmin() && $request->has('owner_id')) ) {
            $query->where('owner_id', $request->user()->id); // limit to my own
            unset($filters['owner_id']);
        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
                case 'owner_id':
                    break;
                default:
            }
        }

        if ($request->has('sortBy')) {
            //switch($request->input('sortBy')) {
            //}
        }


        $data = $query->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new ContentflagCollection($data);
    }

    /**
     * Simple search
     *
     * @param Request $request
     * @return MycontactCollection
     */
    public function search(Request $request)
    {
        $searchQuery = $request->input('query') ?? $request->input('q');

        $data = Mycontact::search($searchQuery)->where('owner_id', $request->user()->getKey())
            ->paginate($request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)));

        return new MycontactCollection($data);
    }

    /**
     * Store new Mycontact
     *
     * @param Request $request
     * @return MycontactResource
     */
    public function store(Request $request)
    {
        $this->authorize('store', Mycontact::class);

        $request->validate([
            'contact_id' => 'required|uuid|exists:users,id',
            'alias'      => 'nullable|string|max:255',
            'cattrs'     => 'nullable|json',
            'meta'       => 'nullable|json',
        ]);

        $mycontact = Mycontact::create(array_merge(
            [ 'owner_id' => $request->user()->getKey() ],
            $request->all(),
        ));

        return new MycontactResource($mycontact);
    }

    /**
     * Update existing Mycontact
     *
     * @param Request   $request
     * @param Mycontact $mycontact
     * @return MycontactResource
     */
    public function update(Request $request, Mycontact $mycontact)
    {
        $this->authorize('update', $mycontact);

        $request->validate([
            'alias'  => 'nullable|string|max:255',
            'cattrs' => 'nullable|json',
            'meta'   => 'nullable|json',
        ]);

        $fields = ['alias', 'cattrs', 'meta'];

        foreach($fields as $field) {
            if ($request->has($field)) {
                $mycontact->{$field} = $request->input($field);
            }
        }

        $mycontact->save();

        return new MycontactResource($mycontact);
    }

    /**
     * Show existing Mycontact
     *
     * @param Mycontact $mycontact
     */
    public function show(Mycontact $mycontact)
    {
        $this->authorize('view', $mycontact);
        return new MycontactResource($mycontact);
    }

    /**
     * Delete existing Mycontact
     *
     * @param Mycontact $mycontact
     */
    public function destroy(Mycontact $mycontact)
    {
        $this->authorize('delete', $mycontact);
        $mycontact->delete();
        return;
    }

}
