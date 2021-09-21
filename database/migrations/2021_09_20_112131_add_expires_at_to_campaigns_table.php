<?php

use App\Models\Campaign;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExpiresAtToCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('message');
        });

        // Populate existing campaigns
        Campaign::where('offer_days', '>', 0)->get()->each(function($campaign) {
            $campaign->expires_at = (new Carbon($campaign->created_at))->addDays($campaign->offer_days);
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
            $table->dropColumn('expires_at');
        });
    }
}
