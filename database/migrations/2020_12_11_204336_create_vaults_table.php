<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultsTable extends Migration
{
    public function up() {
        Schema::create('vaults', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guid')->unique();
            $table->string('slug')->unique();

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('vname')->comment('Vault name');

            $table->longtext('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->longtext('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('vaults');
    }
}
