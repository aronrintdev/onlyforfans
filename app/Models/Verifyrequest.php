<?php
namespace App\Models;

use DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;

use App\Apis\IdMerit\Api as IdMeritApi;
use App\Enums\VerifyServiceTypeEnum;
use App\Enums\VerifyStatusTypeEnum;

class Verifyrequest extends Model
{
    use SoftDeletes;

    protected $guarded = [ 'id', 'created_at', 'updated_at', ];

    //--------------------------------------------
    // Boot
    //--------------------------------------------
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->guid = (string) Uuid::uuid4();
        });
    }

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

    // Sends a verification request to the 3rd-party service
    //   ~ %FIXME %NOTE: tightly coupled to ID Merit service at the moment
    public static function verifyUser(User $user, array $attrs) 
    {
        $now = Carbon::now();

        // do not submit if there's an existing pending reqeust
        $userId = $user->id;
        $pending = Verifyrequest::where('requester_id', $userId)
            ->where('vstatus', VerifyStatusTypeEnum::PENDING)
            ->orderBy('created_at', 'desc')
            ->get();
        try { 
            if ( $pending->count() > 0 ) {
                throw new Exception('verifyUser() - User has verification request currently pending :', $pending[0]->id);
            }
    
            $vr = Verifyrequest::create([
                'requester_id' => $userId,
                'vservice' => VerifyServiceTypeEnum::IDMERIT,
            ]);
    
            $userAttrs = [
	            'mobile' => $attrs['mobile'], // '+94777878905',
	            'name' => $attrs['firstname'].' '.$attrs['lastname'], // 'Dilshan Edirisuriya',
	            'country' => $attrs['country'] ?? null, // 'LK',
	            'dateOfBirth' => $attrs['dob'] ?? null, // '19901231',
	            'requestID' => $vr->guid ?? $vr->id,
	            'callbackURL' => null,
	            //'callbackURL': 'https://devapp.idmvalidate.com/verify/endpoint/success'
            ];
    
            // --

            $api = IdMeritApi::create();
            $response = $api->doVerify($userAttrs);

            $status = $response->status();
            $json = $response->json();
            if ($status !== 200) {
                throw new Exception('verifyUser() - Verify service returned non-200 status: '.$status);
            }

            $json['created_at'] = $now;
            $cattrs = [
                'vrequest_raw_request' => $userAttrs,
                'vrequest_raw_response' => $json,
            ];
            $vr->cattrs = $cattrs;
            $vr->callback_url = $json['callbackURL'] ?? null;
            $vr->qrcode = $json['qrCode'] ?? null;
            $vr->service_guid = $json['uniqueID'] ?? null;
            switch ( $json['status'] ) { // %NOTE - specific to IDMERIT
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
                'message' => $e->getMessage(),
                'userId' => $userId,
                'userAttrs' => $userAttrs ?? 'not-set',
                'json' => $json,
            ]) );
            throw $e;
        }

        return $vr;
    }

    public static function checkStatus(User $user) 
    {
        $now = Carbon::now();

        // only submit if there's an existing pending reqeust
        $userId = $user->id;
        $pending = Verifyrequest::where('requester_id', $userId)
            ->where('vstatus', VerifyStatusTypeEnum::PENDING)
            ->orderBy('created_at', 'desc')
            ->get();
        try { 
            if ( !$pending->count() ) {
                throw new Exception('checkStatus() - User has no verification request currently pending');
            }
    
            $vr = $pending[0];
    
            if ( empty($vr->service_guid) ) {
                throw new Exception('checkStatus() - Could not find guid from remote service request to lookup status: ', $vr->id);
            }

            $uniqueID = $vr->service_guid;

            $api = IdMeritApi::create();
            $response = $api->checkVerify($uniqueID);

            $status = $response->status();
            $json = $response->json();
            if ($status !== 200) {
                throw new Exception('checkStatus() - Verify service returned non-200 status: '.$status);
            }

            $cattrs = $vr->cattrs;
            if ( !array_key_exists('check_verify_status_response', $cattrs) ) {
                $cattrs['check_verify_status_response'] = [];
            }
            $json['created_at'] = $now;
            $cattrs['check_verify_status_response'][] = $json;
            $vr->cattrs = $cattrs;

            $vr->last_checked_at = $now;

            switch ( $json['status'] ?? null ) {
            case 'verified':
                $vr->vstatus =  VerifyStatusTypeEnum::VERIFIED;
                break;
            case 'failed':
                $vr->vstatus =  VerifyStatusTypeEnum::REJECTED;
                break;
            case 'in_progress':
                //$vr->vstatus =  VerifyStatusTypeEnum::PENDING; // no change
                break;
            default:
                throw new Exception('checkStatus() - Unknown status returned: ', $vr->id);
            }
            // ...

            $vr->save();

        } catch (Exception $e) {
            Log::error( json_encode([
                'src' => 'App/Models/Verifyreqeust::checkStatus()',
                'message' => $e->getMessage(),
                'userId' => $userId,
                'userAttrs' => $userAttrs ?? 'not-set',
                'json' => $json,
            ]) );
            throw $e;
        }

        return $vr;
    }


}
