<?php

namespace App\Http\Controllers;

use App\Models\Casts\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Payouts\PayoutGateway;
use App\Models\Financial\Account;

/**
 *
 * @package App\Http\Controllers
 */
class PayoutController extends Controller
{
    /**
     * Request a payout from the system.
     *
     * @param Request $request
     * @return Response
     */
    public function request(Request $request, PayoutGateway $payoutGateway)
    {
        $request->validate([
            'account_id' => 'required|uuid',
            'amount'     => 'required|numeric',
            'currency'   => 'required|size:3',
        ]);

        $outAccount = Account::find($request->account_id);
        $this->authorize('payout', $outAccount);

        $internalAccount = $outAccount->getEarningsAccount();

        // Verify available balance
        $amount = Money::toMoney($request->amount, $request->currency);

        // Update Balance
        $internalAccount->settleBalance();
        if ($amount->greaterThan($internalAccount->balance)) {
            abort(400, 'Request exceeds balance');
        }

        return $payoutGateway->request($internalAccount, $outAccount, $amount);
    }

}
