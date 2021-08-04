<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentflagsTable extends Migration
{
    public function up() {
        Schema::create('contentflags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('cfstatus', 63)->comment('ContentflagType: Enumeration');
            $table->uuidMorphs('flaggable');
            $table->uuid('flagger_id')->comment("FK - User who has flagged this content");
            $table->unique(['flagger_id', 'flaggable_id']);

            $table->longtext('notes')->nullable();
            $table->json('cattrs')->nullable()->comment('JSON-encoded custom attributes');
            $table->json('meta')->nullable()->comment('JSON-encoded meta attributes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('contentflags');
    }
}
