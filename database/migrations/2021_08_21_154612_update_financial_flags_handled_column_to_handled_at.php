<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFinancialFlagsHandledColumnToHandledAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('financial')->table('flags', function (Blueprint $table) {
            $table->dropColumn(['handled']);
        });
        Schema::connection('financial')->table('flags', function (Blueprint $table) {
            $table->timestamp('handled_at')->after('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('financial')->table('flags', function (Blueprint $table) {
            $table->dropColumn(['handled_at']);
        });
        Schema::connection('financial')->table('flags', function (Blueprint $table) {
            $table->boolean('handled')->after('notes')->nullable();
        });
    }
}
