<?php
namespace App\Http\Controllers\Financial;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

use App\Http\Controllers\Controller;
use App\Rules\InEnum;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\Transaction as TransactionResource;
use App\Enums\Financial\AccountTypeEnum;
use App\Enums\Financial\TransactionTypeEnum;
use App\Models\Financial\Account;
use App\Models\Financial\Transaction;
use App\Models\Financial\Report;

class TransactionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();

        $data = $query->paginate($request->input('take', Config::get('collections.max.transactions')));
        return new TransactionCollection($data);
    }

    public function show(Account $account)
    {
        $this->authorize('view', $account);
        return new TransactionResource($account);
    }

    public function summary(Request $request) 
    {
        $counts = DB::connection('financial')->table('transactions')
            ->selectRaw('count(*) as total')
            ->selectRaw("count(case when type = 'sale' then 1 end) as num_sales")
            ->selectRaw("count(case when type = 'payment' then 1 end) as num_payments")
            ->selectRaw("count(case when type = 'chargeback' then 1 end) as num_chargebacks")
            ->selectRaw("count(case when type = 'fee' then 1 end) as num_fees")
            ->first();
        /*
        $sums = DB::connection('financial')->table('transactions')
            ->selectRaw("sum('credit_amount') as sum_sales_credit")
            ->where('type', TransactionTypeEnum::SALE)
            ->first();
         */
        $sums = Report::summary();
        dd($counts, $sums);
    }
}
