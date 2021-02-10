<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /**
             * Account this transaction is tied to
             */
            $table->uuid('account');

            /**
             * The transaction credit amount
             */
            $table->unsignedInteger('credit_amount');

            /**
             * The transaction debit amount
             */
            $table->unsignedInteger('debit_amount');

            /**
             * Account Balance
             */
            $table->unsignedInteger('balance');

            /**
             * Transaction currency code
             */
            $table->string('currency')->default('USD');

            /**
             * Description about the transaction
             */
            $table->string('description');

            /**
             * The other transaction related to this one.
             */
            $table->uuid('reference')->nullable();

            /**
             * Resource related to transaction
             */
            $table->nullableUuidMorphs('resource');

            /**
             * Any additional metadata
             */
            $table->json('metadata');

            /**
             * The time at which the transaction settled.
             * This is null if the transaction is pending.
             */
            $table->timestamp('settled_at')->nullable();

            /**
             * The time a which the transaction failed.
             * This is null if the transaction has not failed.
             */
            $table->timestamp('failed_at')->nullable();

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
        Schema::dropIfExists('financial_transactions');
    }
}
