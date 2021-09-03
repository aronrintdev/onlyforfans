<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveredAtToChatmessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('deliver_at');
        });

        // Transfer created at to delivered at for existing messages
        DB::table('chatmessages')->whereNull('delivered_at')->where('is_delivered', true)
            ->update(['delivered_at' => DB::raw("`created_at`")]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatmessages', function (Blueprint $table) {
            $table->dropColumn('delivered_at');
        });
    }
}
