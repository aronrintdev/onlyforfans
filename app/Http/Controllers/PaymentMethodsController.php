<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Financial\Account;
use App\Enums\Financial\AccountTypeEnum;
use App\Http\Resources\PaymentMethod;
use App\Http\Resources\PaymentMethodCollection;
use Illuminate\Auth\Access\AuthorizationException;

/**
 *
 * @package App\Http\Controllers
 */
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
        $user = $this->getUserFromRequest($request);

        $accounts = $user->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')
            ->paginate($request->input('take', 50));

        return new PaymentMethodCollection($accounts);
    }

    /**
     * Gets the user default payment method, or returns empty if there is none
     * @param Request $request
     * @return array
     */
    public function getDefault(Request $request)
    {
        $user = $this->getUserFromRequest($request);

        $default = $user->settings->cattrs['default_payment_method'] ?? null;

        if (isset($default)) {
            return [
                'default' => new PaymentMethod(Account::find($default))
            ];
        }
        return [ 'default' => null ];
    }

    /**
     * Removes the default payment method by setting the setting to null
     * @param Request $request
     * @return array
     */
    public function removeDefault(Request $request)
    {
        $user = $this->getUserFromRequest($request);
        $user->settings->update(['cattrs->default_payment_method' => null]);
        return [ 'success' => true ];
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

        $user = $this->getUserFromRequest($request);

        $settings = $user->settings;
        $settings->cattrs = array_merge($settings->cattrs, [ 'default_payment_method' => $request->id ]);
        $settings->save();
        $accounts = $user->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')
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
