<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableFinancialTransactionSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->create('transaction_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            /**
             * Transaction this summary starts at
             */
            $table->uuid('from_transaction_id')->nullable();
            /**
             * Transaction this summary ends at
             */
            $table->uuid('to_transaction_id')->nullable();

            /**
             * Type of summary this is. I.E Daily, Monthly, Yearly
             */
            $table->string('type');

            /**
             * Declares if the summary is finalized, New summaries for an account **should not** be created until the
             * latest summary is finalized, any calculations/reporting should only be taken from finalized summaries.
             */
            $table->boolean('finalized')->default(false);

            /**
             * The verified final balance of the account at `to_transaction`
             */
            $table->bigInteger('balance')->nullable();
            /**
             * The amount of change in the balance between `from_transaction` and `to_transaction`
             */
            $table->bigInteger('balance_delta')->nullable();
            /**
             * How many transaction are included in this summary
             */
            $table->unsignedBigInteger('transactions_count')->nullable();

            /**
             * Sums of the credit_amount and debit_amount
             */
            $table->bigInteger('credit_sum')->nullable();
            $table->bigInteger('debit_sum')->nullable();

            /**
             * Averages for this period
             */
            $table->bigInteger('credit_average')->nullable();
            $table->bigInteger('debit_average')->nullable();

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
        Schema::connection('financial')->dropIfExists('transaction_summaries');
    }
}
