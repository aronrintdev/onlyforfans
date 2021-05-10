<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeFinancialTransactionFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('transactions', function (Blueprint $table) {
            $table->string('description')->nullable()->change();
            $table->json('metadata')->nullable()->change();
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
            $table->string('description')->change();
            $table->json('metadata')->change();
        });
    }
}
