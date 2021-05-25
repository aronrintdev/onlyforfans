<?php

namespace App\Http\Controllers;

use App\Rules\InEnum;
use Illuminate\Http\Request;
use App\Models\Financial\AchAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Enums\Financial\AccountTypeEnum;
use App\Enums\Financial\AchAccountTypeEnum;
use App\Http\Resources\AchAccountCollection;
use App\Enums\Financial\AchAccountBankTypeEnum;
use App\Http\Resources\AchAccount as AchAccountResource;

/**
 *
 * @package App\Http\Controllers
 */
class AchAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     * If logged in user only display owned ach accounts
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = AchAccount::orderBy('created_at');

        // viewAny permission for admins only
        if ($request->user()->cannot('viewAny', AchAccount::class) || $request->missing('all')) {
            $query = $query->where('user_id', $request->user()->getKey());
        }

        if ($request->has('trashed')) {
            $query = $query->trashed();
        } else if ($request->has('with_trashed') ) {
            $query = $query->withTrashed();
        }

        $data = $query->paginate($request->input('take', Config::get('collections.max.ach_accounts')));
        return new AchAccountCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|max:255',
            'type'              => ['required', new InEnum(new AchAccountTypeEnum())],
            'residence_country' => 'required|size:2',
            'beneficiary_name'  => 'required|max:255',
            'bank_name'         => 'required|max:255',
            'routing_number'    => 'required|numeric|size:9',
            'account_number'    => 'required|numeric',
            'account_type'      => ['required', new InEnum(new AchAccountBankTypeEnum())],
            'bank_country'      => 'required|size:2',
            'currency'          => 'required|size:3',
        ]);

        // New Out Account
        $account = $request->user()->financialAccounts()->create([
            'system' => Config::get('transactions.default'),
            'currency' => $request->currency,
            'name' => $request->user()->username . ' Ach Account',
            'type' => AccountTypeEnum::OUT,
        ]);
        $account->verified = true;
        $account->can_make_transactions = true;
        $account->save();

        $achAccount = $account->resource()->create($request->all());

        return new AchAccountResource($achAccount);
    }

    /**
     * Display the specified resource.
     *
     * @param  AchAccount  $bank_account
     * @return \Illuminate\Http\Response
     */
    public function show(AchAccount $bank_account)
    {
        $this->authorize('view', $bank_account);

        return new AchAccountResource($bank_account);
    }

    public function setDefault(Request $request)
    {
        $request->validate([
            'id' => 'required|uuid',
        ]);

        $settings = Auth::user()->settings;
        $settings->cattrs = array_merge($settings->cattrs, [ 'default_payout_method' => $request->id ]);
        $settings->save();

        return [];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  AchAccount  $bank_account
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AchAccount $bank_account)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  AchAccount  $bank_account
     * @return \Illuminate\Http\Response
     */
    public function destroy(AchAccount $bank_account)
    {
        $this->authorize('delete', $bank_account);

        $bank_account->delete();

        return [];
    }

    /**
     * Force remove a Ach Account from the DB. This should only be able to be done
     *  by admins.
     *
     * @param AchAccount $bank_account
     * @return \Illuminate\Http\Response
     */
    public function forceDelete(AchAccount $bank_account)
    {
        $this->authorize('forceDelete', $bank_account);

        $bank_account->forceDelete();

        return [];
    }
}
