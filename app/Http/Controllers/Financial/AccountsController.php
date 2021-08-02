<?php
namespace App\Http\Controllers\Financial;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

use App\Http\Controllers\Controller;
use App\Rules\InEnum;
use App\Http\Resources\AccountCollection;
use App\Http\Resources\Account as AccountResource;
use App\Enums\Financial\AccountTypeEnum;
//use App\Enums\Financial\AchAccountTypeEnum;
//use App\Enums\Financial\AchAccountBankTypeEnum;
use App\Models\Financial\Account;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::query();

        if ($request->has('trashed')) {
            $query = $query->trashed();
        } else if ($request->has('with_trashed') ) {
            $query = $query->withTrashed();
        }

        $data = $query->paginate($request->input('take', Config::get('collections.max.accounts')));
        return new AccountCollection($data);
    }

    public function show(Account $account)
    {
        $this->authorize('view', $account);
        return new AccountResource($account);
    }
}
