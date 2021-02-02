<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

//use App\Enums\PostTypeEnum;
use App\Enums\PaymentTypeEnum;
use App\Fanledger;
use App\Post;
use App\Timeline;
use App\User;

class ShareablesTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     *  @group group_shareables
     *  @group regression
     */
    public function test_can_follow_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first();
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = User::has('followedtimelines','<>',$timeline->id)->first(); // not yet a follower of timeline

        $payload = [
            'sharee_id' => $fan->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('shareables.followTimeline', $timeline->id), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $shareableR = $content->shareable;

        $shareable = Timeline::find($shareableR->id);
        $this->assertNotNull($shareable);
        $this->assertEquals($timeline->id, $shareable->id);
        $this->assertEquals('default', $shareable->followers->find($fan->id)->pivot->access_level);
        $this->assertEquals('timelines', $shareable->followers->find($fan->id)->pivot->shareable_type);
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $shareable->id ) );


        // %TODO: unfollow
    }

    /**
     *  @group group_shareables
     *  @group OFF-this
     */
    public function test_can_subscribe_to_timeline()
    {
        $timeline = Timeline::has('posts','>=',1)->has('followers','>=',1)->first(); // includes subscribers
        $creator = $timeline->user;
        $post = $timeline->posts[0];
        $fan = User::has('followedtimelines','<>',$timeline->id)->first(); // not yet a follower (includes susbcribers) of timeline

        $payload = [
            'sharee_id' => $fan->id,
        ];
        $response = $this->actingAs($fan)->ajaxJSON('PUT', route('shareables.subscribeTimeline', $timeline->id), $payload);

        $response->assertStatus(200);

        $content = json_decode($response->content());
        $shareableR = $content->shareable;

        $shareable = Timeline::find($shareableR->id);
        $this->assertNotNull($shareable);
        $this->assertEquals($timeline->id, $shareable->id);
        $this->assertEquals('premium', $shareable->followers->find($fan->id)->pivot->access_level);
        $this->assertEquals('timelines', $shareable->followers->find($fan->id)->pivot->shareable_type);
        $this->assertTrue( $timeline->followers->contains( $fan->id ) );
        $this->assertTrue( $fan->followedtimelines->contains( $shareable->id ) );

        // %TODO: unsubscribe (will not be charged next recurring payment period)
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

