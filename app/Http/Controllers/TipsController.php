<?php

namespace App\Http\Controllers;

use App\Helpers\Tippable;
use App\Models\Financial\Account;
use App\Models\Tip;
use App\Payments\PaymentGateway;
use Illuminate\Http\Request;

class TipsController extends Controller
{
    /**
     * Display a listing of my tips.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'currency'        => 'required|size:3',
            'amount'          => 'required|numeric',
            'tippable_id'     => 'uuid',
            'receiver_id'     => 'required_without:tippable_id|uuid|exists:users:id',
            'message'         => 'string',
            'period'          => 'string',
            'period_interval' => 'numeric',
        ]);

        if ($request->has('tippable_id')) {
            $tippableItem = Tippable::getTippableItem($request->tippable_id);
            if (!isset($tippableItem)) {
                abort(422, 'tippable_id not found');
            }
            $receiverId = $tippableItem->getOwner()->first()->getKey();
        } else {
            $receiverId = $request->receiver_id;
        }

        $tip = Tip::create([
            'sender_id'       => $request->user()->getKey(),
            'receiver_id'     => $receiverId,
            'currency'        => $request->currency,
            'amount'          => $request->amount,
            'period'          => $request->period ?? 'single',
            'period_interval' => $request->period_interval ?? 1,
            'message'         => $request->message ?? null,
        ]);

        if ($request->has('tippable_id')) {
            $tip->tippable()->associate($tippableItem)->save();
        }

        return $tip;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tip  $tip
     * @return \Illuminate\Http\Response
     */
    public function show(Tip $tip)
    {
        $this->authorize('view', $tip);

        return $tip;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tip  $tip
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tip $tip)
    {
        $this->authorize('update', $tip);

        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tip  $tip
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tip $tip)
    {
        $this->authorize('delete', $tip);

        $tip->delete();
    }

    /**
     * Starts the processing for this tip
     *
     * @param  \Illuminate\Http\Request  $request
     * @param Tip $tip
     * @param PaymentGateway $paymentGateway
     * @return \Illuminate\Http\Response
     */
    public function process(Request $request, Tip $tip, PaymentGateway $paymentGateway)
    {
        $this->authorize('tip', $tip);

        $request->validate([
            'account_id' => 'required|uuid',
        ]);

        $account = Account::find($request->account_id);
        if (!isset($account)) {
            abort('422', 'Account does not exist');
        }

        return $paymentGateway->tip($account, $tip->tippable, $tip->amount);
    }


}
