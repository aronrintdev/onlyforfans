<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThumbnailFieldsToMediafiles extends Migration
{
    public function up()
    {
        Schema::table('mediafiles', function (Blueprint $table) {
            $table->unsignedInteger('orig_size')->nullable()->after('slug')->comment('In megabytes');
            $table->boolean('has_mid')->default(false)->after('slug');
            $table->boolean('has_thumb')->default(false)->after('slug');
        });
    }

    public function down()
    {
        Schema::table('mediafiles', function (Blueprint $table) {
            $table->dropColumn([
                'has_mid',
                'has_thumb',
                'orig_size',
            ]);
        });
    }
}
