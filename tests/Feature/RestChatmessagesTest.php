<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\User;
use App\Models\Chatthread;
use App\Models\Chatmessage;
use App\Models\Mycontact;

class RestChatmessagesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group chatmessages
     *  @group regression
     */
    public function test_can_list_chatmessages()
    {
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());

        $payload = [ 
            'take' => 100,
        ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('chatmessages.index', $payload) );

        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);

        $response->assertJsonStructure([
            'data' => [
                0 => [
                    'chatthread_id', 
                    'sender_id', 
                    'mcontent', 
                    'deliver_at', 
                    'is_delivered', 
                    'is_read', 
                    'is_flagged', 
                    'created_at', 
                ],
            ],
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        //dd($content->messages);

        // Check no messages from threads in which I am not a participant
        $chatmessages = collect($content->data);
        $num = $chatmessages->reduce( function($acc, $cm) use(&$sessionUser) {
            $chatmessage = Chatmessage::find($cm->id);
            $this->assertNotNull($chatmessage);
            return ($chatmessage->chatthread->participants->contains($sessionUser->id)) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread in which session user is not a participant (of '.$content->meta->total.' total messages)');

        // Make sure no scheduled but undelivered messages are returned
        $num = $chatmessages->reduce( function($acc, $cm) {
            return $cm->is_delivered ? $acc : ($acc+1); // expect is_delivered to be TRUE
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread which is not marked "delivered"');
    }


    /**
     *  @group chatmessages
     *  @group regression
     */
    public function test_can_list_chatmessages_filtered_by_thread()
    {
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());
        $chatthread = $sessionUser->chatthreads[0];

        $payload = [ 'take' => 100, 'chatthread_id' => $chatthread->id ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('chatmessages.index', $payload) );

        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);

        // Check all messages belong to indicated thread
        $chatmessages = collect($content->data);
        $num = $chatmessages->reduce( function($acc, $cm) use(&$chatthread) {
            $_cm = Chatmessage::findOrFail($cm->id);
            return ($_cm->chatthread->id===$chatthread->id) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread other than the one filtered on');

        // Make sure no scheduled but undelivered messages are returned
        $num = $chatmessages->reduce( function($acc, $cm) {
            return $cm->is_delivered ? $acc : ($acc+1); // expect is_delivered to be TRUE
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread which is not marked "delivered"');
    }

    /**
     *  @group chatmessages
     *  @group regression
     *  @group here0607
     */
    public function test_can_list_mycontacts()
    {
        $sessionUser = User::has('mycontacts')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());

        $payload = [ 'take' => 100 ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('mycontacts.index', $payload) );

        $response->assertStatus(200);
        $content = json_decode($response->content());

        $mycontacts = collect($content->data);
        $this->assertGreaterThan(0, $mycontacts->count());

        // Check all mycontacts belong to session user
        $num = $mycontacts->reduce( function($acc, $mc) use(&$sessionUser) {
            return ($mc->owner_id===$sessionUser->id) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found contact in results that does not belong to session user');
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

