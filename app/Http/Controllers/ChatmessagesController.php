<?php
namespace App\Http\Controllers;

use DB;
use Exception;
use Throwable;
use App\Models\User;
use App\Models\Mediafile;
use App\Models\Chatthread;
use App\Models\Casts\Money;
use App\Models\Chatmessage;
use Illuminate\Http\Request;
use App\Payments\PaymentGateway;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Http\Resources\MediafileCollection;
use App\Http\Resources\ChatmessageCollection;
use App\Http\Resources\Chatmessage as ChatmessageResource;

class ChatmessagesController extends AppBaseController
{
    public function index(Request $request)
    {
        $request->validate([
            // filters
            'chatthread_id'  => 'uuid|exists:chatthreads,id',
            'sender_id'      => 'uuid|exists:users,id',
            'participant_id' => 'uuid|exists:users,id',
            'is_flagged'     => 'boolean',
        ]);
        $filters = $request->only([
            'chatthread_id',
            'sender_id',
            'participant_id',
            'is_flagged',
        ]) ?? [];

        $query = Chatmessage::query(); // Init query

        // %NOTE: this method only returns delivered messages! ...use separate api endpoint
        //   for all messages or messages to be delivered
        $query->where('is_delivered', true); 

        // Check permissions
        if ( true || !$request->user()->isAdmin() ) {
            //$query->where('user_id', $request->user()->id); // non-admin: can only view own...
            //unset($filters['user_id']);

            $query->whereHas('chatthread.participants', function($q1) use(&$request) {
                $q1->where('user_id', $request->user()->id); // limit to threads where session user is a participant
            });

            if ( array_key_exists('chatthread_id', $filters) ) {
                $chatthread = Chatthread::findOrFail($filters['chatthread_id']);
                $this->authorize('view', $chatthread);
            }
            if ( array_key_exists('sender_id', $filters) ) {
            }
            if ( array_key_exists('participant_id', $filters) ) {
            }
        }

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
            case 'sender_id':
                $query->where('sender_id', $v); // %FIXME: if non-admin limit 
                break;
            case 'participant_id':
                $query->whereHas('chatthread.participants', function($q1) use($key, $v) {
                    $q1->where('user_id', $v);
                });
                break;
            default:
                $query->where($key, $v);
            }
        }

        $data = $query->latestDelivered()->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new ChatmessageCollection($data);
    }


    public function search(Request $request)
    {
        $request->validate([
            'chatthread' => 'required|uuid|exists:chatthreads,id',
            'q'          => 'required_without:query',
            'query'      => 'required_without:q',
        ]);

        $chatthread = Chatthread::find($request->chatthread);

        $this->authorize('view', $chatthread);

        $query = $request->input('q') ?? $request->input('query');

        $data = Chatmessage::search($query)->where('chatthread_id', $chatthread->id)
            ->paginate($request->input('take', Config::get('collections.size.mid', 20) ));

        return new ChatmessageCollection($data);
    }

    /**
     * Return a paginated list of images that are in a chatthread
     * @param Request $request
     * @return void
     */
    public function gallery(Request $request, Chatthread $chatthread)
    {
        // $request->validate([
        //     'chatthread_id' => 'required|uuid|exists:chatthreads,id',
        // ]);

        $this->authorize('view', $chatthread);

        $query = Mediafile::whereHasMorph('resource', Chatmessage::class, function($q1) use ($chatthread) {
            $q1->where('chatthread_id', $chatthread->getKey());
        });

        $data = $query->latest()->paginate($request->input('take', Config::get('collections.size.mid', 20)));

        return new MediafileCollection($data);
    }

    /**
     * Purchase Message
     */
    public function purchase(Request $request, Chatmessage $chatmessage, PaymentGateway $paymentGateway)
    {
        $this->authorize('purchase', $chatmessage);

        $request->validate([
            'account_id' => 'required|uuid',
            'amount' => 'required|numeric',
            'currency' => 'required',
        ]);

        $price = Money::toMoney($request->amount, $request->currency);
        if ($chatmessage->verifyPrice($price) === false) {
            abort(400, 'Invalid Price');
        }

        $account = Account::with('resource')->find($request->account_id);
        $this->authorize('purchase', $account);

        return $paymentGateway->purchase($account, $chatmessage, $price);
    }

    public function destroy(Chatmessage $chatmessage)
    {
        if (($chatmessage->purchase_only && $chatmessage->sharees()->count() === 0) ||
            (!$chatmessage->purchase_only && !$chatmessage->is_read)
        ) {
            $chatmessage->delete();
            return response()->json(200);
        } else {
            return response()->json(['error' => 'read already.'], 401);
        }
    }

}
