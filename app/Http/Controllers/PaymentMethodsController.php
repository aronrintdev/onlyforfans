<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\Auth;
use App\Enums\Financial\AccountTypeEnum;
use App\Http\Resources\PaymentMethodCollection;
use Illuminate\Auth\Access\AuthorizationException;

class PaymentMethodsController extends Controller
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
}
