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

        $data = $query->latest()->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new ChatmessagegroupCollection($data);
    }

}
