<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialCurrencyExchangeTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_currency_exchange_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            /**
             * User that initiated the transaction.
             */
            $table->uuid('initiated_by')->nullable();
            /**
             * Account transaction is to.
             */
            $table->uuid('credit_account');
            /**
             * Account transaction is from.
             */
            $table->uuid('debit_account');

            /**
             * Credit amount and currency designator.
             */
            $table->unsignedInteger('credit_amount');
            $table->string('credit_currency')->default('USD');

            /**
             * Debit amount and currency designator.
             */
            $table->unsignedInteger('debit_amount');
            $table->string('debit_currency')->default('USD');

            /**
             * The exchange rate when this transaction was recorded.
             */
            $table->unsignedDecimal('exchange_rate', 8, 14);

            /**
             * Any additional metadata
             */
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_currency_exchange_transactions');
    }
}
