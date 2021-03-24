<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThumbnailFieldsToMediafiles extends Migration
{
    public function up()
    {
        Schema::table('mediafiles', function (Blueprint $table) {
            $table->unsignedInteger('orig_size')->nullable()->after('slug');
            $table->boolean('has_blur')->default(false)->after('slug');
            $table->boolean('has_mid')->default(false)->after('slug');
            $table->boolean('has_thumb')->default(false)->after('slug');
            $table->string('basename')->nullable()->after('slug');
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('mediafiles', function (Blueprint $table) {
            $table->dropColumn([
                'basename',
                'has_thumb',
                'has_mid',
                'has_blur',
                'orig_size',
            ]);
            $table->dropSoftDeletes();
        });
    }
}
