<?php

namespace App\Http\Controllers;

use App\Enums\Financial\AccountTypeEnum;
use App\Http\Resources\PaymentMethodCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentsController extends Controller
{


    /**
     * Retrieves the logged in user's payment methods
     *
     * @param Request $request
     * @return array
     */
    public function myPaymentMethods(Request $request)
    {
        $user = Auth::user();

        $accounts = $user->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')
            ->paginate($request->input('take', env('MAX_POSTS_PER_REQUEST', 10)));

        return new PaymentMethodCollection($accounts);
    }
}
