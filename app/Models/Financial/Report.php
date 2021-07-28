<?php
namespace App\Models\Financial;

use DB;
use Carbon\Carbon;
use App\Enums\Financial\AccountTypeEnum;
use App\Enums\Financial\TransactionTypeEnum;

class Report {

    // currently for admin only!
    public static function summary(array $attrs=[])
    {
        $DAYS_BACK = 365*10;

        $from = array_key_exists('from', $attrs) ? new Carbon($attrs['from']) : Carbon::now()->subDays($DAYS_BACK);
        $to = array_key_exists('to', $attrs) ? new Carbon($attrs['to']) : Carbon::now();

        $credits = Transaction::select('type', DB::raw('SUM(credit_amount) as total, COUNT(*) as count'))
            ->where('credit_amount', '>', 0)->orderBy('settled_at', 'desc')
            ->whereIn('type', [
                TransactionTypeEnum::SALE,
                TransactionTypeEnum::TIP,
                TransactionTypeEnum::SUBSCRIPTION,
            ])
            ->whereBetween('settled_at', [ $from, $to ])
            ->groupBy('type')
            ->get();

        $debits = Transaction::select('type', DB::raw('SUM(debit_amount) as total, COUNT(*) as count'))
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
}
