<?php

namespace App\Http\Controllers;

use App\Rules\InEnum;
use Illuminate\Http\Request;
use App\Enums\PaymentTypeEnum;
use App\Jobs\FakeSegpayPayment;
use App\Models\Financial\Account;
use App\Models\Financial\SegpayCard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Enums\Financial\AccountTypeEnum;
use App\Helpers\Tippable as TippableHelpers;
use App\Http\Resources\PaymentMethodCollection;
use App\Helpers\Purchasable as PurchasableHelpers;
use App\Helpers\Subscribable as SubscribableHelpers;
use Illuminate\Auth\Access\AuthorizationException;

class PaymentsController extends Controller
{


    /**
     * Retrieves the logged in user's payment methods
     *
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $accounts = $user->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')
            ->paginate($request->input('take', 50));

        return new PaymentMethodCollection($accounts);
    }


    /**
     * Sets a users default payment method
     * @param Request $request
     * @return void
     */
    public function setDefault(Request $request)
    {
        $request->validate([
            'id' => 'required|uuid',
        ]);

        $settings = Auth::user()->settings;
        $settings->cattrs = array_merge($settings->cattrs, [ 'default_payment_method' => $request->id ]);
        $settings->save();
        $accounts = Auth::user()->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')
            ->paginate($request->input('take', 50));
        return new PaymentMethodCollection($accounts);
    }

    /**
     * Soft delete payment method
     * @param Request $request
     * @return void
     * @throws AuthorizationException
     */
    public function remove(Request $request)
    {
        $request->validate([
            'id' => 'required|uuid',
        ]);

        $account = Account::find($request->id);
        $this->authorize('delete', $account);

        if ( $account->has('resource') ) {
            $account->resource->delete();
        }
        $account->delete();
    }

    public function purchase(Request $request)
    {
        $request->validate([
            'item' => 'required|uuid',
            'type' => [ 'required', new InEnum(new PaymentTypeEnum()) ],
            'price' => 'required|integer',
            'currency' => 'required',
            'method' => 'required|uuid',
        ]);

        // Get payment account
        $account = Account::find($request->method);

        // Get payment item
        if ($request->type === PaymentTypeEnum::PURCHASE) {
            $item = PurchasableHelpers::getPurchasableItem($request->item);
        } else if ($request->type === PaymentTypeEnum::TIP) {
            $item = TippableHelpers::getTippableItem($request->item);
        } else if ($request->type === PaymentTypeEnum::SUBSCRIPTION) {
            $item = SubscribableHelpers::getSubscribableItem($request->item);
        }

        if (get_class($account->resource) == SegpayCard::class) {
            if (Config::get('segpay.fake') && Config::get('app.env') != 'production') {
                // Dispatch Event
                FakeSegpayPayment::dispatch($item, $account, $request->type, $request->price, $request->extra ?? null);
                return;
            }

            // Call Segpay Api Here
            $account->resource->token;
        }

    }

}
