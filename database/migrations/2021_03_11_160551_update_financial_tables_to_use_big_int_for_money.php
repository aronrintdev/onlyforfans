<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFinancialTablesToUseBigIntForMoney extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('accounts', function (Blueprint $table) {
            $table->bigInteger('balance')->nullable()->change();
            $table->bigInteger('pending')->nullable()->change();
        });
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('credit_amount')->nullable()->change();
            $table->unsignedBigInteger('debit_amount')->nullable()->change();
            $table->bigInteger('balance')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->table('accounts', function (Blueprint $table) {
            $table->integer('balance')->nullable()->change();
            $table->integer('pending')->nullable()->change();
        });
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->unsignedInteger('credit_amount')->nullable()->change();
            $table->unsignedInteger('debit_amount')->nullable()->change();
            $table->unsignedInteger('balance')->nullable()->change();
        });
    }
}
