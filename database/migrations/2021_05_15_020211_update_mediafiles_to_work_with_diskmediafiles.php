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

            $table->string('mfname')->comment('Mediafile name');
            $table->string('mftype', 63)->comment('Mediafile Type: Enumeration');
            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');

            $table->timestamps();
            $table->softDeletes();
        });
        /*
        Schema::table('mediafiles', function (Blueprint $table) {
            $table->uuid('diskmediafile_id')->nullable()->after('id'); // nullable for sqlite workaround
            $table->foreign('diskmediafile_id')->references('id')->on('diskmediafiles');
        });

        // workaround for sqlite
        Schema::table('mediafiles', function (Blueprint $table) {
            $table->renameColumn('filename', 'deprecated_filename');
        });
        Schema::table('mediafiles', function(Blueprint $table) {
            $table->renameColumn('mimetype', 'deprecated_mimetype');
        });
        Schema::table('mediafiles', function(Blueprint $table) {
            $table->renameColumn('orig_ext', 'deprecated_orig_ext');
        });
        Schema::table('mediafiles', function(Blueprint $table) {
            $table->renameColumn('orig_filename', 'deprecated_orig_filename');
        });
        Schema::table('mediafiles', function(Blueprint $table) {
            $table->renameColumn('has_blur', 'deprecated_has_blur');
        });
        Schema::table('mediafiles', function(Blueprint $table) {
            $table->renameColumn('has_mid', 'deprecated_has_mid');
        });
        Schema::table('mediafiles', function(Blueprint $table) {
            $table->renameColumn('has_thumb', 'deprecated_has_thumb');
        });
        Schema::table('mediafiles', function(Blueprint $table) {
            $table->renameColumn('orig_size', 'deprecated_orig_size');
        });
        Schema::table('mediafiles', function(Blueprint $table) {
            $table->renameColumn('basename', 'deprecated_basename');
        });
         */
    }

    public function down()
    {
        Schema::dropIfExists('mediafiles');
        /*
        Schema::table('mediafiles', function (Blueprint $table) {
            //$table->dropForeign('mediafiles_diskmediafile_id_foreign');
            $table->dropColumn([
                'diskmediafile_id',
            ]);

            $table->renameColumn('deprecated_filename', 'filename');
            $table->renameColumn('deprecated_mimetype', 'mimetype');
            $table->renameColumn('deprecated_orig_ext', 'orig_ext');
            $table->renameColumn('deprecated_orig_filename', 'orig_filename');
            $table->renameColumn('deprecated_has_blur', 'has_blur');
            $table->renameColumn('deprecated_has_mid', 'has_mid');
            $table->renameColumn('deprecated_has_thumb', 'has_thumb');
            $table->renameColumn('deprecated_orig_size', 'orig_size');
            $table->renameColumn('deprecated_basename', 'basename');
        });
         */
        Schema::create('mediafiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // $table->string('guid')->unique();
            $table->string('slug')->unique();

            $table->nullableUuidMorphs('resource');

            $table->string('filename')->comment('Filename as stored, in S3 for ex');
            $table->string('mfname')->comment('Mediafile name');
            $table->string('mftype', 63)->comment('Mediafile Type: Enumeration');

            $table->unsignedInteger('orig_size')->nullable()->after('slug');
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
