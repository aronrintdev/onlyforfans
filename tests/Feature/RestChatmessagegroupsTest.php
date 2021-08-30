<?php
namespace Tests\Feature;

use DB;
use Carbon\Carbon;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

use Illuminate\Foundation\Testing\WithFaker;

use Database\Seeders\TestDatabaseSeeder;
use Tests\TestCase;

use App\Events\MessageSentEvent;

use App\Models\Chatthread;
use App\Models\Chatmessage;
use App\Models\User;
use App\Models\Timeline;

class RestChatmessagegroupsTest extends TestCase
{
    use WithFaker;

    /**
     *  @group chatmessagegroups
     *  @group regression
     *  @group regression-base
     */
    public function test_can_send_mass_message()
    {
        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
        $originator = $timeline->user;
        $fans = $timeline->followers;

        $this->assertGreaterThan(0, $fans->count());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $fans->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        //dd($content);
        $response->assertStatus(201);

        $response->assertJsonStructure([
            'chatthreads' => [
                0 => [ 'id', 'originator_id', 'is_tip_required', 'created_at' ],
            ],
            'chatmessagegroup' => [ 'id', 'mgtype', 'sender_id' ],
            'chatmessages' => [ 'id', 'chatthread_id', 'mcontent', 'sender_id', 'deliver_at', 'is_delivered', 'is_read', 'is_flagged' ],
        ]);

        $this->assertEquals($fans->count(), count($content->chatthreads));

        $ctR = Chatthread::find($content->chatthreads[0]->id);
        //dd($ctR->chatmessages);
        $this->assertNotNull($ctR);
        $this->assertNotNull($ctR->id);
        $this->assertEquals(2, $ctR->participants->count());

        // This will *not* necessarily be true, because we made threads 'singletons', it's 
        // possible the originator may not be the one pushing the mass message (ie it may be sent
        // as a response to a fan who originated the 1-to-1 chat
        //$this->assertEquals($originator->id, $ctR->originator->id);

        // Because chatthreads.store returns an array of threads (not the created messages), in addition
        // to the above, it doesn't seem like we can locate the exact message created by the 
        // chatthreads.store call above (?)
    }

    /**
     *  @group chatmessagegroups
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_chatmessagegroups()
    {
        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
        $originator = $timeline->user;
        $fans = $timeline->followers;

        $this->assertGreaterThan(0, $fans->count());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $fans->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);

        $ctR = Chatthread::find($content->chatthreads[0]->id);
        $this->assertNotNull($ctR->id);

        $payload = [];
        $response = $this->actingAs($originator)->ajaxJSON( 'GET', route('chatmessagegroups.index', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();
        //$this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

