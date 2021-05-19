<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Increasing timestamp precision on financial transactions table
 */
class UpdateFinancialTransactionsTimestampAccuracy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->dropColumn(['settled_at', 'failed_at', 'created_at', 'updated_at']);
        });
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->timestamp('settled_at', 6)->nullable();
            $table->timestamp('failed_at', 6)->nullable();
            $table->timestamps(6);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->dropColumn(['settled_at', 'failed_at', 'created_at', 'updated_at']);
        });
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->timestamp('settled_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }
}
