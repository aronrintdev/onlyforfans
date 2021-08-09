<?php

namespace App\Http\Controllers;

use App\Enums\Financial\TransactionTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Http\Resources\TransactionCollection;
use App\Models\Financial\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
            'from' => 'date',
            'to' => 'date',
        ]);

        $DAYS_BACK = 3000;
        $from = $request->has('from') ? new Carbon($request->from) : Carbon::now()->subDays($DAYS_BACK);
        $to   = $request->has('to')   ? new Carbon($request->to)   : Carbon::now();

        // Get summary items
        $user = Auth::user();
        $account = $user->getEarningsAccount(Config::get('transactions.default'), Config::get('transactions.defaultCurrency'));
        $credits = $account->transactions()
            ->select('type', DB::raw('SUM(credit_amount) as total, COUNT(*) as count'))
            ->where('credit_amount', '>', 0)->orderBy('settled_at', 'desc')
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
            ->where('debit_amount', '>', 0)->orderBy('settled_at', 'desc')
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

        return [
            'credits' => [
                TransactionTypeEnum::SALE => $credits->firstWhere('type', TransactionTypeEnum::SALE) ?? [ 'total' => 0, 'count' => 0 ],
                TransactionTypeEnum::TIP => $credits->firstWhere('type', TransactionTypeEnum::TIP) ?? ['total' => 0, 'count' => 0],
                TransactionTypeEnum::SUBSCRIPTION => $credits->firstWhere('type', TransactionTypeEnum::SUBSCRIPTION) ?? ['total' => 0, 'count' => 0],
            ],
            'debits' => [
                TransactionTypeEnum::FEE => $debits->firstWhere('type', TransactionTypeEnum::FEE) ?? ['total' => 0, 'count' => 0],
                TransactionTypeEnum::CHARGEBACK => $chargeback,
                TransactionTypeEnum::CREDIT => $debits->firstWhere('type', TransactionTypeEnum::CREDIT) ?? ['total' => 0, 'count' => 0],
            ],
            'from' => $from,
            'to' => $to,
        ];
    }

    public function balances(Request $request)
    {
        $account = $request->user()->getEarningsAccount(
            Config::get('transactions.default'),
            Config::get('transactions.defaultCurrency')
        );

        return [
            'available' => $account->balance,
            'pending' => $account->pending,
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
        $user = Auth::user();
        $account = $user->getEarningsAccount(Config::get('transactions.default'), Config::get('transactions.defaultCurrency'));
        $query = $account->transactions()->where('credit_amount', '>', 0)->orderBy('settled_at', 'desc')
            ->whereIn('type', [ TransactionTypeEnum::SALE, TransactionTypeEnum::TIP, TransactionTypeEnum::SUBSCRIPTION ]);

        $data = $query->paginate($request->input('take', Config::get('collections.max.transactions', 20)));
        return new TransactionCollection($data);
    }
}
