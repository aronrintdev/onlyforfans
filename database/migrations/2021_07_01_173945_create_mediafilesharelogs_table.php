<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediafilesharelogsTable extends Migration
{
    public function up()
    {
        Schema::create('mediafilesharelogs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('sharer_id')->comment("user who is sharing");
            $table->foreign('sharer_id')->references('id')->on('users');

            $table->uuid('sharee_id')->comment("user being shared to");
            $table->foreign('sharee_id')->references('id')->on('users');

            $table->uuid('vaultfolder_id')->comment("Share vaultfolder created to hold shared content (files) in sharee's vault");
            $table->foreign('vaultfolder_id')->references('id')->on('vaultfolders');

            $table->boolean('is_approved')->default(false);

            $table->json('cattrs')->nullable()->comment("JSON-encoded custom attributes");
            $table->json('meta')->nullable()->comment("JSON-encoded meta attributes");

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mediafilesharelogs');
    }
}
