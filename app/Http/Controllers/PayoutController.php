<?php

namespace App\Http\Controllers;

use App\Models\Casts\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Payouts\PayoutGateway;
use App\Models\Financial\Account;
use App\Models\Financial\AchAccount;

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
            'account_id' => 'required_without:ach_id|uuid',
            'ach_id'     => 'required_without:account_id',
            'amount'     => 'required|numeric',
            'currency'   => 'required|size:3',
        ]);

        if ($request->has('ach_id')) {
            $outAccount = AchAccount::find($request->ach_id)->account;
        } else {
            $outAccount = Account::find($request->account_id);
        }

        $this->authorize('payout', $outAccount);

        $earningsAccount = $outAccount->getEarningsAccount();

        // Verify available balance
        $amount = Money::toMoney($request->amount, $request->currency);

        // Update Balance
        $earningsAccount->settleBalance();
        if ($amount->greaterThan($earningsAccount->balance)) {
            abort(400, 'Request exceeds balance');
        }

        return $payoutGateway->request($earningsAccount, $outAccount, $amount);
    }

}
