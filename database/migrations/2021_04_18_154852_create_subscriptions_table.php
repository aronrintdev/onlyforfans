<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('subscribable');
            $table->uuid('user_id');
            $table->uuid('account_id')->nullable();
            $table->boolean('manual_charge')->default(true);
            $table->string('period')->default('monthly');
            $table->unsignedInteger('period_interval')->default(1);
            $table->unsignedBigInteger('price');
            $table->string('currency', 3)->default('USD');
            $table->boolean('active');
            $table->string('access_level')->default('premium');
            $table->json('custom_attributes')->nullable();
            $table->json('metadata')->nullable();
            $table->uuid('last_transaction_id')->nullable();
            $table->timestamp('next_payment_at')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
