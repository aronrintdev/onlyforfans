<?php

namespace App\Jobs;

use App\Enums\PaymentTypeEnum;
use App\Events\ItemPurchased;
use App\Events\PurchaseFailed;
use App\Models\Financial\Account;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($item, Account $account, $type, $price)
    {
        $this->item = $item;
        $this->account = $account;
        $this->type = $type;
        $this->price = $price;
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
                PurchaseFailed::dispatch($this->item, $this->account);
            }
        }

        if ($this->type === PaymentTypeEnum::TIP) {
            //
        }

        if ($this->type === PaymentTypeEnum::SUBSCRIPTION) {
            //
        }

    }
}
