<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransactionSummariesFromToColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('transaction_summaries', function (Blueprint $table) {
            $table->timestamp('from')->after('account_id')->nullable();
        });
        Schema::connection('financial')->table('transaction_summaries', function (Blueprint $table) {
            $table->timestamp('to')->after('from')->nullable();
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
            $table->dropColumn([ 'to', 'from' ]);
        });
    }
}
