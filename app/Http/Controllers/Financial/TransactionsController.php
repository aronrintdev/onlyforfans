<?php
namespace App\Http\Controllers\Financial;

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
}
