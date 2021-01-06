<?php
namespace App\Libs;

use Illuminate\Support\Str;
use Illuminate\Support\Collection; 
use Illuminate\Support\Facades\Log;

use DB;
use Exception;
use App\User;
use App\Fanledger;
use App\Post;
use App\Enums\PaymentTypeEnum;
use App\Interfaces\PaymentSendable;
use App\Interfaces\PaymentReceivable;

/*
    [ ] notifications
    [ ] ledger
    [ ] payment processor transaction(s)
    [ ] misc: eg post->tip()->attach()
 */
class Payment {

    public static function __construct(
        PaymentTypeEnum $ptype,
        PaymentSendable $sender,
        PaymentReceivable $receiver,
        int $amountInCents,
        array $cattrs = []
    ) {

        throw new Exception('Deprecate - not needed');

        $result = DB::transaction( function() {

            switch ($ptype) {
                case PaymentTypeEnum::TIP:
                    $receiver->tip()->attach( $sessionUser->id, [ // receiver is a Post
                        'amount' => $amount, 
                        'note' => $request->note
                    ]);
                    break;
                case PaymentTypeEnum::SUBSCRIBE:
                    $sender->usersSentTips()->attach( $sender->id, [
                        'amount' => $amount,
                        'note' => $request->note,
                        'tip_to' => $receiver->id
                    ]);
                    break;
                default:
                    throw new Exception('Unrecognized payment type : '.$ptype);
            }

            return $result;
        });

    }

}
