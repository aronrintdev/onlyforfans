<?php
namespace App\Http\Controllers;

use DB;
use Exception;
use Throwable;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

use App\Http\Resources\MediafileCollection;
use App\Http\Resources\ChatmessagegroupCollection;
use App\Http\Resources\Chatmessagegroup as ChatmessageResourcegroup;

use App\Models\User;
use App\Models\Mediafile;
use App\Models\Chatthread;
use App\Models\Chatmessage;
use App\Models\Chatmessagegroup;

class ChatmessagegroupsController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'sender_id'  => 'uuid|exists:users,id',
            'mgtype'     => 'string|in:mass-message',
            'qsearch'    => 'string', // not a filter
        ]);
        $filters = $request->only([
            'sender_id',
            'mgtype',
        ]) ?? [];

        $query = Chatmessagegroup::query(); // Init query

        // Check permissions | Restrict
        $query->where('sender_id', $request->user()->id);

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
            default:
                $query->where($key, $v);
            }
        }

        // Text search
        if ( $request->has('qsearch') && (strlen($request->qsearch)>2) ) {
            $query->where( function($q1) use(&$request) {
                $q1->where('mcontent', 'LIKE', '%'.$request->qsearch.'%');
                $q1->orWhere('id', 'LIKE', $request->qsearch.'%');
                $q1->orWhere('sender_id', 'LIKE', $request->qsearch.'%');
            });
        }

        // Sorting
        switch ($request->sortBy) {
        case 'created_at':
        case 'updated_at':
        case 'mgtype':
        case 'sender_id':
            $sortDir = $request->sortDir==='asc' ? 'asc' : 'desc';
            $query->orderBy($request->sortBy, $sortDir);
            break;
        default:
            $query->orderBy('updated_at', 'desc');
        }

        $data = $query->latest()->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new ChatmessagegroupCollection($data);
    }

}
