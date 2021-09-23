<?php
namespace App\Models;

use DB;
use Exception;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Collection;

use App\Enums\VerifyStatusTypeEnum;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\URL;
use App\Enums\VerifyServiceTypeEnum;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use App\Apis\Idmerit\Api as IdMeritApi;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\IdentityVerificationRejected;
use App\Notifications\IdentityVerificationVerified;

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
        return $this->belongsTo(User::class, 'requester_id');
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
            $json = null; // default
            if ( $pending->count() > 0 ) {
                throw new Exception('verifyUser() - User has verification request currently pending :', $pending[0]->id);
            }

            // Limit number of verification attempts a user can try
            if (Verifyrequest::where('requester_id', $userId)->count() > Config::get('verification.maxAttempts', 3)) {
                throw new Exception('User has reached max number of verification requests.');
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
	            'callbackURL' => url((route('webhook.receive.id-merit'))), // URL to webhook receive endpoint
	            //'callbackURL': 'https://devapp.idmvalidate.com/verify/endpoint/success'
            ];
    
            // --

            Log::info('App\Models\Verifyrequest::verifyUser().A -- calling Api/doVerify(): '.json_encode([
                'userAttrs' => $userAttrs,
            ]));

            $api = IdMeritApi::create();
            $response = $api->doVerify($userAttrs);

            $status = $response->status();
            $json = $response->json();
            if ($status !== 200) {
                throw new Exception('verifyUser() - Verify service returned non-200 status: '.$status);
            }

            $json['created_at'] = $now;
            Log::info('App\Models\Verifyrequest::verifyUser().B -- processing response: '.json_encode([
                'json' => $json,
            ]));

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
                'src' => 'App/Models/Verifyreqeust::verifyUser().EXCEPTION',
                'message' => $e->getMessage(),
                'userId' => $userId,
                'userAttrs' => $userAttrs ?? 'not-set',
                'json' => $json ?? [],
            ]) );
            throw $e;
        }

        return $vr;
    }

    // Updates the Verifyrequest DB record
    public static function checkStatusByGUID(string $guid) 
    {
        $now = Carbon::now();

        $vr = Verifyrequest::where('guid', $guid)->first();

        try { 
            $json = null; 
            if ( !$vr ) {
                throw new Exception('App/Models/Verifyrequest::checkStatusByGUID() - Could not find vr with guid '.$guid);
            }
    
            if ( empty($vr->service_guid) ) {
                throw new Exception('App/Models/Verifyrequest::checkStatusByGUID() - Could not find guid from remote service request to lookup status: ', $vr->guid);
            }

            $uniqueID = $vr->service_guid;

            Log::info('App\Models\Verifyrequest::checkStatusByGUID().A -- calling Api/checkStatus(): '.json_encode([
                'uniqueID (service_guid)' => $uniqueID,
            ]));

            $api = IdMeritApi::create();
            $response = $api->checkVerify($uniqueID);

            $status = $response->status();
            $json = $response->json();
            if ($status !== 200) {
                throw new Exception('App/Models/Verifyrequest::checkStatusByGUID() - Verify service returned non-200 status: '.$status);
            }

            Log::info('App\Models\Verifyrequest::checkStatusByGUID().B -- processing response: '.json_encode([
                'json' => $json,
            ]));

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
                $vr->requester->notify(new IdentityVerificationVerified($vr, $vr->requester));
                break;
            case 'failed':
                $vr->vstatus =  VerifyStatusTypeEnum::REJECTED;
                $vr->requester->notify(new IdentityVerificationRejected($vr, $vr->requester));
                break;
            case 'in_progress':
                //$vr->vstatus =  VerifyStatusTypeEnum::PENDING; // no change
                break;
            default:
                throw new Exception('App/Models/Verifyrequest::checkStatusByGUID() - Unknown status returned: ', $vr->id);
            }
            // ...

            $vr->save();

        } catch (Exception $e) {
            Log::error( json_encode([
                'src' => 'App/Models/Verifyreqeust::checkStatusByGUID()',
                'message' => $e->getMessage(),
                // 'userId' => $userId,
                'userAttrs' => $userAttrs ?? 'not-set',
                'json' => $json || [],
            ]) );
            throw $e;
        }

        return $vr;
    }

}
