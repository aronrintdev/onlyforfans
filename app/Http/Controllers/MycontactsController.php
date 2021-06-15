<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Resources\MycontactCollection;
use App\Http\Resources\Mycontact as MycontactResource;
use App\Models\Mycontact;
use App\Models\User;

/**
 * Mycontacts Resource Controller
 * @package App\Http\Controllers
 */
class MycontactsController extends AppBaseController
{
    /**
     * Fetch list of contacts with filter
     *
     * @param Request $request
     * @return MycontactCollection
     */
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'owner_id' => 'uuid|exists:users,id',
        ]);
        $filters = $request->only(['owner_id']) ?? [];

        $query = Mycontact::query(); // Init query

        // Check permissions
        if ( !$request->user()->isAdmin() ) {

            $query->where('owner_id', $request->user()->id); // limit to my own
            unset($filters['owner_id']);

        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
            default:
                $query->where($key, $v);
            }
        }

        $data = $query->latest()->paginate( $request->input('take', env('MAX_DEFAULT_PER_REQUEST', 10)) );
        return new MycontactCollection($data);
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
