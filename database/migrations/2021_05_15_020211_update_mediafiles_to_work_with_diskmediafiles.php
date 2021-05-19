<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMediafilesToWorkWithDiskmediafiles extends Migration
{
    public function up()
    {
        Schema::dropIfExists('mediafiles');

        Schema::create('mediafiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();

            $table->uuid('diskmediafile_id');
            $table->foreign('diskmediafile_id')->references('id')->on('diskmediafiles');

            $table->nullableUuidMorphs('resource');

            $table->boolean('is_primary')->default(false)->comment('True if this is the original mediafile associated with the disk image');
            $table->string('mfname')->comment('Mediafile name');
            $table->string('mftype', 63)->comment('Mediafile Type: Enumeration');
            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mediafiles');
        Schema::create('mediafiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // $table->string('guid')->unique();
            $table->string('slug')->unique();

            $table->nullableUuidMorphs('resource');

            $table->string('filename')->comment('Filename as stored, in S3 for ex');
            $table->string('mfname')->comment('Mediafile name');
            $table->string('mftype', 63)->comment('Mediafile Type: Enumeration');

            $table->unsignedInteger('orig_size')->nullable();
            $table->boolean('has_blur')->default(false);
            $table->boolean('has_mid')->default(false);
            $table->boolean('has_thumb')->default(false);
            $table->string('basename')->nullable();

            $table->string('mimetype', 255)->nullable();
            $table->string('orig_ext', 15)->nullable();
            $table->string('orig_filename', 511)->nullable();

            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
        });

    }

}
