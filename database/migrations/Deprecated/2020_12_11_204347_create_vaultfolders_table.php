<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVaultfoldersTable extends Migration
{
    public function up() {
        Schema::create('vaultfolders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guid')->unique();
            $table->string('slug')->unique();

            $table->unsignedInteger('parent_id')->nullable()->comment('Parent fodler, NULL for root');
            $table->foreign('parent_id')->references('id')->on('vaultfolders');

            $table->unsignedInteger('vault_id');
            $table->foreign('vault_id')->references('id')->on('vaults');

            $table->string('name')->comment('Vault folder name');

            $table->longtext('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->longtext('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('vaultfolders');
    }
}
