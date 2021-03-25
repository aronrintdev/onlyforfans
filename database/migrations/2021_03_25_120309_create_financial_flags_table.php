<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialFlagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->nullableUuidMorphs('model');
            $table->string('column')->nullable();
            $table->string('delta_before')->nullable();
            $table->string('delta_after')->nullable();
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('handled')->default(false);
            $table->uuid('handled_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_flags');
    }
}
