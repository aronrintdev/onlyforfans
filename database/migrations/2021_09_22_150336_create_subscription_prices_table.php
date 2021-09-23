<?php

use App\Enums\SubscriptionPeriodEnum;
use Money\Money;
use App\Models\Timeline;
use App\Models\UserSetting;
use App\Models\SubscriptionPrice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('subscribeable');
            $table->bigInteger('price');
            $table->string('currency', 3);
            $table->string('period');
            $table->integer('period_interval');
            $table->json('custom_attributes')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Transfer over old values
        // From timeline price first
        Timeline::where('price', '>', 0)->get()->each(function($timeline) {
            $timeline->updateOneMonthPrice($timeline->price);
        });

        // Then attempt from user_setting
        UserSetting::whereJsonLength('cattrs->subscriptions', '>', 0)->get()->each(function($user_setting) {
            $timeline = $user_setting->user->timeline;
            $subscriptions = new Collection($user_setting->cattrs['subscriptions']);
            $subscriptions->each(function ($subscription, $key) use ($timeline) {
                $price = Money::USD(intval((float)$subscription * 100));
                switch($key) {
                    case 'price_per_1_months':
                        SubscriptionPrice::updatePrice($timeline, $price, SubscriptionPeriodEnum::DAILY, 30);
                        break;
                    case 'price_per_3_months':
                        SubscriptionPrice::updatePrice($timeline, $price, SubscriptionPeriodEnum::DAILY, 60);
                        break;
                    case 'price_per_6_months':
                        SubscriptionPrice::updatePrice($timeline, $price, SubscriptionPeriodEnum::DAILY, 120);
                        break;
                    case 'price_per_12_months':
                        SubscriptionPrice::updatePrice($timeline, $price, SubscriptionPeriodEnum::DAILY, 360);
                        break;
                }
            });
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_prices');
    }
}
