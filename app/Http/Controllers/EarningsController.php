<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Financial\Transaction;
use Illuminate\Support\Facades\Config;
use App\Enums\Financial\TransactionTypeEnum;
use App\Models\Financial\TransactionSummary;
use App\Http\Resources\TransactionCollection;
use App\Enums\Financial\TransactionSummaryTypeEnum;

class EarningsController extends Controller
{

    /**
     * Earnings Summary for the logged in user
     *
     * @param Request $request
     * @return array
     */
    public function index(Request $request)
    {
        $request->validate([
            'from'     => 'date',
            'to'       => 'date',
            'ago_unit' => 'string',
            'ago'      => 'numeric',
        ]);

        $DAYS_BACK = 3000;
        $ago_unit = $request->has('ago_unit') ? $request->ago_unit : 'day';
        $ago = $request->has('ago') ? $request->ago : $DAYS_BACK;

        $from = $request->has('from') ? new Carbon($request->from) : Carbon::now()->sub($ago_unit, $ago);
        $to   = $request->has('to')   ? new Carbon($request->to)   : Carbon::now();

        [ $system, $currency ] = $this->systemAndCurrency($request);

        // Get summary items
        $user = Auth::user();
        $account = $user->getEarningsAccount($system, $currency);
        $credits = $account->transactions()
            ->select('type', DB::raw('SUM(credit_amount) as total, COUNT(*) as count'))
            ->isCredit()->settled()
            ->whereIn('type', [
                TransactionTypeEnum::SALE,
                TransactionTypeEnum::TIP,
                TransactionTypeEnum::SUBSCRIPTION,
            ])
            ->whereBetween('settled_at', [ $from, $to ])
            ->groupBy('type')
            ->get();

        $debits = $account->transactions()
            ->select('type', DB::raw('SUM(debit_amount) as total, COUNT(*) as count'))
            ->isDebit()->settled()
            ->whereIn('type', [
                TransactionTypeEnum::FEE,
                TransactionTypeEnum::CHARGEBACK,
                TransactionTypeEnum::CHARGEBACK_PARTIAL,
                TransactionTypeEnum::CREDIT,
            ])
            ->whereBetween('settled_at', [$from, $to])
            ->groupBy('type')
            ->get();

        $credits->each(function($item, $key) {
            $item['total'] = (int)$item['total'];
        });

        $debits->each(function ($item, $key) {
            $item['total'] = (int)$item['total'];
        });

        $chargeback = $debits->firstWhere('type', TransactionTypeEnum::CHARGEBACK) ?? ['total' => 0, 'count' => 0];
        $chargebackPartial = $debits->firstWhere('type', TransactionTypeEnum::CHARGEBACK_PARTIAL) ?? ['total' => 0, 'count' => 0];
        $chargeback['total'] += $chargebackPartial['total'];
        $chargeback['count'] += $chargebackPartial['count'];

        $summaries = TransactionSummary::getBatchAgo($account, $ago_unit, $ago);

        return [
            'credits' => [
                TransactionTypeEnum::SALE =>
                    $credits->firstWhere('type', TransactionTypeEnum::SALE) ?? [ 'total' => 0, 'count' => 0 ],
                TransactionTypeEnum::TIP =>
                    $credits->firstWhere('type', TransactionTypeEnum::TIP) ?? ['total' => 0, 'count' => 0],
                TransactionTypeEnum::SUBSCRIPTION =>
                    $credits->firstWhere('type', TransactionTypeEnum::SUBSCRIPTION) ?? ['total' => 0, 'count' => 0],
            ],
            'debits' => [
                TransactionTypeEnum::FEE => $debits->firstWhere('type', TransactionTypeEnum::FEE) ?? ['total' => 0, 'count' => 0],
                TransactionTypeEnum::CHARGEBACK => $chargeback,
                TransactionTypeEnum::CREDIT => $debits->firstWhere('type', TransactionTypeEnum::CREDIT) ?? ['total' => 0, 'count' => 0],
            ],
            'from' => $from,
            'to' => $to,
            'ago' => $ago,
            'ago_unit' => $ago_unit,
            'summaries' => $summaries,
        ];
    }

    /**
     * Retrieves the balances for the current logged in user.
     */
    public function balances(Request $request)
    {
        [ $system, $currency ] = $this->systemAndCurrency($request);

        $user = $request->user();
        if ($request->user()->isAdmin()) {
            $request->validate(['user_id' => 'uuid|exists:users,id' ]);
            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            }
        }

        $account = $user->getEarningsAccount($system, $currency);

        return [
            'balance' => $account->balance->subtract($account->pending),
            'balance_last_updated_at' => $account->balance_last_updated_at,
            'pending' => $account->pending,
            'pending_last_updated_at' => $account->pending_last_updated_at,
        ];
    }

    /**
     * Earning Transactions for an account
     *
     * @param Request $request
     * @return void
     */
    public function transactions(Request $request)
    {
        $request->validate([
            'from' => 'date',
            'to' => 'date',
        ]);

        [ $system, $currency ] = $this->systemAndCurrency($request);

        $user = Auth::user();
        $account = $user->getEarningsAccount($system, $currency);
        $query = $account->transactions()
            ->where('credit_amount', '>', 0)
            ->orderBy('settled_at', 'desc')
            ->whereIn('type', Config::get("transactions.systems.$system.feesOn")); // Get only transactions that have fees taken out on them

        $data = $query->paginate($request->input('take', Config::get('collections.max.transactions', 20)));
        return new TransactionCollection($data);
    }

    /**
     * Shared system and currency prams from request.
     *
     * @param Request $request
     * @return array [ 'system', 'currency' ]
     */
    private function systemAndCurrency(Request $request): array
    {
        $request->validate([
            'system'   => 'string',
            'currency' => 'size:3',
        ]);

        return [
            $request->has('system')   ? $request->system   : Config::get('transactions.default'),
            $request->has('currency') ? $request->currency : Config::get('transactions.defaultCurrency'),
        ];
    }


}
