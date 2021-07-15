<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

use App\Apis\IdMerit\Api as IdMeritApi;
use App\Models\Traits\UsesUuid;
use App\Enums\VerifyServiceTypeEnum;
use App\Enums\VerifyStatusTypeEnum;

class Verifyrequest extends Model
{
    use UsesUuid;

    protected $guarded = [ 'id', 'created_at', 'updated_at', ];

    //--------------------------------------------
    // Accessors/Mutators | Casts
    //--------------------------------------------

    protected $casts = [ 'cattrs' => 'array', 'meta' => 'array', ];

    //--------------------------------------------
    // Relationships
    //--------------------------------------------

    public function requester() {
        return $this->belongsTo(User::class, requester_id);
    }

    //--------------------------------------------
    // Other Methods
    //--------------------------------------------

    // Sends a verification request for the user
    public static function verifyUser(User $user, array $attrs) 
    {
        // do not submit if there's an existing pending reqeust
        $userId = $user->id;
        $pending = Verifyrequest::where('requester_id', $userId)
            ->where('vstatus', VerifyStatusTypeEnum::PENDING)
            ->orderBy('created_at', 'desc')
            ->get();
        try { 
            if ( $pending->count() > 0 ) {
                throw new Exception('User has verification request currently pending :', $pending[0]->id);
            }
    
            $vr = Verifyrequest::create([
                'requester_id' => $userId,
                'vservice' => VerifyServiceTypeEnum::IDMERIT,
            ]);
    
            $userAttrs = [
	            'mobile' => $attrs['mobile'], // '+94777878905',
	            'name' => $attrs['firstname'].' '.$attrs['lastname'], // 'Dilshan Edirisuriya',
	            'country' => $attrs['country'], // 'LK',
	            'dateOfBirth' => $attrs['dob'], // '19901231',
	            'requestID' => $vr->id,
	            'callbackURL' => null,
	            //'callbackURL': 'https://devapp.idmvalidate.com/verify/endpoint/success'
            ];
    
            // --

            $api = IdMeritApi::create();
            $response = $api->doVerify($userAttrs);

            $status = $response->status();
            if ($status !== 200) {
                throw new Exception('Verify service returned non-200 status: '.$status);
            }
            $json = $response->json();

            $cattrs = [
                'request_raw_response' => $json,
            ];
            $vr->cattrs = $cattrs;
            //$vr->last_checked_at = 
            $vr->callback_url = $json->callbackURL;
            $vr->qrcode = $json->qrCode;
            switch ( json('status'] ) { // %NOTE - specific to IDMERIT
            case 'in_progress':
                $vr->vstatus = VerifyStatusTypeEnum::PENDING;
                break;
            case 'failed':
                $vr->vstatus = VerifyStatusTypeEnum::REJECTED;
                break;
            case 'verified':
                $vr->vstatus = VerifyStatusTypeEnum::VERIFIED;
                break;
            default:
                $vr->vstatus = null;
            }
            $vr->save();

        } catch (Exception $e) {
            Log::error( json_encode([
                'src' => 'App/Models/Verifyreqeust::verifyUser()',
                'message' => $e->getMessage();
                'userId' => $userId,
                'userAttrs' => $userAttrs,
            ]) );
            throw $e;
        }

        return $vr;

    }

    public static function checkStatus(User $user) 
    {
        $userId = $user->id;
        $vr = Verifyrequest::where('requester_id', $userId)->orderBy('created_at', 'desc')->first(); // get latest request
    }


}
