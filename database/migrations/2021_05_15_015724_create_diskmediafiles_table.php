<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiskmediafilesTable extends Migration
{
    public function up()
    {
        Schema::create('diskmediafiles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('owner_id');
            $table->foreign('owner_id')->references('id')->on('users');

            $table->string('filepath')->comment('Filename/path as stored, in S3 for ex');
            $table->string('mimetype', 255)->nullable();
            $table->string('orig_ext', 15)->nullable();
            $table->string('orig_filename', 511);
            $table->string('basename');
            $table->boolean('has_blur')->default(false);
            $table->boolean('has_mid')->default(false);
            $table->boolean('has_thumb')->default(false);
            $table->unsignedInteger('orig_size')->nullable();

            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diskmediafiles');
    }
}
