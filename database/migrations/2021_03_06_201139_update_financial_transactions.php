<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFinancialTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            /**
             * Changing to ISO_4217 Currency code length
             * https://en.wikipedia.org/wiki/ISO_4217
             */
            $table->string('currency', 3)->change();
            $table->renameColumn('reference', 'reference_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->string('currency')->change();
            $table->renameColumn('reference_id', 'reference');
        });
    }
}
