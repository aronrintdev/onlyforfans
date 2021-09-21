<?php

use App\Models\Campaign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemainingCountToCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->integer('remaining_count')->nullable()->after('message');
        });

        Campaign::where('subscriber_count', '>', 0)->get()->each(function ($campaign) {
            $campaign->remaining_count = $campaign->subscriber_count - $campaign->subscriptions()->count();
            $campaign->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('remaining_count');
        });
    }
}
