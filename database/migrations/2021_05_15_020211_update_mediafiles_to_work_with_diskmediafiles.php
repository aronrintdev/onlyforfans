<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMediafilesToWorkWithDiskmediafiles extends Migration
{
    public function up()
    {
        Schema::table('mediafiles', function (Blueprint $table) {

            $table->uuid('diskmediafile_id')->nullable()->after('id'); // nullable for sqlite workaround
            $table->foreign('diskmediafile_id')->references('id')->on('diskmediafiles');

            $table->renameColumn('filename', 'deprecated_filename');
            $table->renameColumn('mimetype', 'deprecated_mimetype');
            $table->renameColumn('orig_ext', 'deprecated_orig_ext');
            $table->renameColumn('orig_filename', 'deprecated_orig_filename');
            $table->renameColumn('has_blur', 'deprecated_has_blur');
            $table->renameColumn('has_mid', 'deprecated_has_mid');
            $table->renameColumn('has_thumb', 'deprecated_has_thumb');
            $table->renameColumn('orig_size', 'deprecated_orig_size');
            $table->renameColumn('basename', 'deprecated_basename');
        });
    }

    public function down()
    {
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
    }
}
