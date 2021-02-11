<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaults', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('vname')->comment('Vault name');

            $table->json('custom_attributes')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('metadata')->nullable()->comment('JSON-encoded metadata attributes');

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
        Schema::dropIfExists('vaults');
    }
}
