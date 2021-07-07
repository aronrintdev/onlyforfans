<?php

namespace App\Jobs;

use Exception;
use App\Events\TipFailed;
use App\Events\ItemPurchased;
use Illuminate\Bus\Queueable;
use App\Enums\PaymentTypeEnum;
use App\Events\ItemSubscribed;
use App\Events\PurchaseFailed;
use App\Models\Financial\Account;
use App\Events\SubscriptionFailed;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;

/**
 * Fakes an payment, must have environment variable turned on to use
 *
 * @package App\Jobs
 */
class FakeSegpayPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $item;

    public $account;

    public $type;

    public $price;

    public $extra;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($item, Account $account, $type, $price, $extra = [])
    {
        $this->item = $item;
        $this->account = $account;
        $this->type = $type;
        $this->price = $price;
        $this->extra = $extra;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (Config::get('app.env') === 'production' || Config::get('segpay.fake') === false) {
            return;
        }

        if ($this->type === PaymentTypeEnum::PURCHASE) {
            try {
                $this->account->purchase($this->item, $this->price);
            } catch(Exception $e) {
                Log::warning('Purchase Failed to process', ['e' => $e->__toString()]);
                PurchaseFailed::dispatch($this->item, $this->account);
            }
        }

        if ($this->type === PaymentTypeEnum::TIP) {
            try {
                $this->item->process(true, [ 'account_id' => $this->account->id ]);
                // $this->account->tip($this->item, $this->price, [ 'message' => $this->extra['message'] ?? '' ]);
            } catch (Exception $e) {
                Log::warning('Tip Failed to process', ['e' => $e->__toString()]);
                TipFailed::dispatch($this->item, $this->account);
            }
        }

        if ($this->type === PaymentTypeEnum::SUBSCRIPTION) {
            try {
                $subscription = $this->account->createSubscription($this->item, $this->price, [
                    'manual_charge' => false,
                ]);
                $subscription->process();
                ItemSubscribed::dispatch($this->item, $this->account->owner);
            } catch (Exception $e) {
                Log::warning('Subscription Failed to be created', [ 'e' => $e->__toString() ]);
                SubscriptionFailed::dispatch($this->item, $this->account);
            }
        }

    }
}
