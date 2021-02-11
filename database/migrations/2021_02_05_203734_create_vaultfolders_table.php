<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultfoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaultfolders', function (Blueprint $table) {
            $table->uuid('id')->primary;
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            // $table->string('guid')->unique();
            $table->string('slug')->unique();

            $table->uuid('parent_id')->nullable()->comment('Parent folder, NULL for root');

            $table->uuid('vault_id');
            $table->foreign('vault_id')->references('id')->on('vaults');

            $table->string('vfname')->comment('Vault folder name');

            $table->json('custom_attributes')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('metadata')->nullable()->comment('JSON-encoded meta attributes');

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
        Schema::dropIfExists('vaultfolders');
    }
}
