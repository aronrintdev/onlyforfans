<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatsColumnToTransactionSummaries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('transaction_summaries', function (Blueprint $table) {
            $table->json('stats')->nullable()->after('debit_average');
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
            $table->dropColumn(['stats']);
        });
    }
}
