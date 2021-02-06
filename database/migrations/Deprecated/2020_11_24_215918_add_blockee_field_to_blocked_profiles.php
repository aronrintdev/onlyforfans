<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBlockeeFieldToBlockedProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blocked_profiles', function (Blueprint $table) {
            $table->unsignedInteger('blockee_id')->nullable()->after('blocked_by')->comment('FK to [users]');
            $table->string('ip_address')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blocked_profiles', function (Blueprint $table) {
            $table->dropColumn(['blockee_id']);
        });
    }
}
