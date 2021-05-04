<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Http\Resources\TransactionCollection;
use App\Models\Financial\Transaction;

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
        //
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
        $account = $user->getInternalAccount(Config::get('transactions.default'), Config::get('transactions.defaultCurrency'));
        $query = $account->transactions()->orderBy('settled_at', 'desc');

        $data = $query->paginate($request->input('take', Config::get('collections.max.transactions', 20)));
        return new TransactionCollection($data);
    }
}
