<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVaultFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vault_folders', function (Blueprint $table) {
            $table->uuid('id')->primary;
            $table->string('guid')->unique();
            $table->string('slug')->unique();

            $table->uuid('parent_id')->nullable()->comment('Parent folder, NULL for root');
            $table->foreign('parent_id')->references('id')->on('vaultfolders');

            $table->uuid('vault_id');
            $table->foreign('vault_id')->references('id')->on('vaults');

            $table->string('vfname')->comment('Vault folder name');

            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');

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
        Schema::dropIfExists('vault_folders');
    }
}
