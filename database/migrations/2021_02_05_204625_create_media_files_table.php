<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('guid')->unique();
            $table->string('slug')->unique();
            $table->string('filename')->nullable()->comment('Filename as stored, in S3 for ex')->unique();

            $table->string('mfname')->comment('Mediafile name');
            $table->string('mftype', 63)->comment('MediaFile Type: Enumeration');

            $table->string('mimetype', 255)->nullable();
            $table->string('orig_ext', 15)->nullable();
            $table->string('orig_filename', 511)->nullable();
            $table->nullableUuidMorphs('resource');
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
        Schema::dropIfExists('media_files');
    }
}
