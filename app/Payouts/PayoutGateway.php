<?php

namespace App\Payouts;

use App\Enums\Financial\PayoutBatchTypeEnum;
use App\Enums\Financial\TransactionTypeEnum;
use Money\Money;
use App\Models\Financial\Account;
use App\Models\Financial\AchAccount;
use App\Models\Financial\PayoutBatch;
use Illuminate\Support\Facades\Log;

class PayoutGateway implements PayoutGatewayContract
{
    public function request(Account $from, Account $to, Money $amount)
    {
        try {
            switch($to->resource_type) {
                case AchAccount::getMorphStringStatic():
                    $this->achAccountRequest($from, $to, $amount);
                    break;
            }
            return [
                'success' => false,
                'message' => 'Account resource does not supported by payout gateway'
            ];
        } catch(\Exception $e) {
            Log::error('Error processing Payout Request', [
                'message' => $e->getMessage(),
                'stacktrace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'There was an error processing this payout'
            ];
        }
    }

    /**
     * Requests payout for a ach account
     *
     * @param Account $from
     * @param Account $to
     * @param Money   $amount
     * @return array
     */
    protected function achAccountRequest(Account $from, Account $to, Money $amount)
    {
        // Get latest batch collector
        $batch = PayoutBatch::currentBatch(PayoutBatchTypeEnum::SEGPAY_ACH);

        $from->moveTo($to, $amount, [
            'type' => TransactionTypeEnum::PAYOUT,
            'resource' => $batch,
        ]);

        return [
            'success' => true,
            'message' => 'Payout Request was processed'
        ];
    }

}
