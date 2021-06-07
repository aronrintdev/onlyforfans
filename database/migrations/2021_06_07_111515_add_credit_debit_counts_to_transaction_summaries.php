<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditDebitCountsToTransactionSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('transaction_summaries', function (Blueprint $table) {
            $table->unsignedInteger('credit_count')->nullable()->after('transactions_count');
        });
        Schema::connection('financial')->table('transaction_summaries', function (Blueprint $table) {
            $table->unsignedInteger('debit_count')->nullable()->after('credit_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->table('transaction_summaries', function (Blueprint $table) {
            $table->dropColumn(['credit_count', 'debit_count']);
        });
    }
}
