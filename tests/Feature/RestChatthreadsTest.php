<?php
namespace Tests\Feature;

use Carbon\Carbon;
use DB;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\User;
use App\Models\Chatthread;
use App\Models\Chatmessage;

class RestChatthreadsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group chatthreads
     *  @group regression
     */
    public function test_can_list_chatthreads()
    {
        //$originator = User::doesntHave('chatthreads')->firstOrFail();
        //$receiver = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->firstOrFail();
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());

        $payload = [ 
            'take' => 100,
        ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('chatthreads.index', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);

        $response->assertJsonStructure([
            'data' => [
                0 => [
                    'originator_id', 
                    'created_at', 
                ],
            ],
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        //dd($content);
        //dd($content->messages);

        // Check no threads in which I am not a participant
        $chatthreads = collect($content->data);
        $num = $chatthreads->reduce( function($acc, $cm) use(&$sessionUser) {
            $chatthread = Chatthread::find($cm->id);
            $this->assertNotNull($chatthread);
            return ($chatthread->participants->contains($sessionUser->id)) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found thread in which session user is not a participant (of '.$content->meta->total.' total threads)');
    }

    /**
     *  @group chatthreads
     *  @group regression
     */
    public function test_can_list_chatthreads_for_single_participant()
    {
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());

        $payload = [ 
            'participant_id' => $sessionUser->id,
            'take' => 100,
        ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('chatthreads.index', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());

        // Check no threads in which I am not a participant
        $chatthreads = collect($content->data);
        $num = $chatthreads->reduce( function($acc, $cm) use(&$sessionUser) {
            $chatthread = Chatthread::find($cm->id);
            $this->assertNotNull($chatthread);
            return ($chatthread->participants->contains($sessionUser->id)) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found thread in which session user is not a participant (of '.$content->meta->total.' total threads)');
    }

    /**
     *  @group chatthreads
     *  @group regression
     */
    public function test_can_create_chat_thread_with_selected_participants()
    {
        $originator = User::doesntHave('chatthreads')->firstOrFail();
        $participants = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->take(3)->get();
        $this->assertGreaterThan(0, $participants->count());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $participants->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        //$content = json_decode($response->content());
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                    'id', 
                    'originator_id', 
                    'is_tip_required', 
                    'created_at', 
            ],
        ]);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group here0521
     */
    public function test_can_send_message()
    {
        // create chat
        $originator = User::doesntHave('chatthreads')->firstOrFail();
        $otherParticipants = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->take(3)->get();
        $this->assertGreaterThan(0, $otherParticipants->count());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $otherParticipants->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        //dd($content);
        $response->assertStatus(201);
        $chatthreadPKID = $content->data->id;

        // send some messages

        $msgs = [];
        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            $content->data->id, // chatthread_id
            'mcontent' => $msg,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.sendMessage', $payload) );
        $response->assertStatus(201);

        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            $content->data->id, // chatthread_id
            'mcontent' => $msg,
        ];
        $response = $this->actingAs($otherParticipants[0])->ajaxJSON( 'POST', route('chatthreads.sendMessage', $payload) );
        $response->assertStatus(201);

        // --
        $chatthread = Chatthread::find($chatthreadPKID);
        $this->assertNotNull($chatthread);
        $this->assertEquals(2, $chatthread->chatmessages->count());
        $this->assertEquals((1+$otherParticipants->count()), $chatthread->participants->count());
        $this->assertTrue($chatthread->participants->contains($originator->id));
        $this->assertTrue($chatthread->participants->contains($otherParticipants[0]->id));

        // Check no messages from threads in which I am not a participant
        $num = $otherParticipants->reduce( function($acc, $p) use(&$chatthread) {
            return ( $chatthread->participants->contains($p->id) ) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found participant that was not included in the chatthread');

        $this->assertEquals($msgs[0], $chatthread->chatmessages[0]->mcontent);
        $this->assertEquals($msgs[1], $chatthread->chatmessages[1]->mcontent);
    }

    /**
     *  @group chatthreads
     *  @group regression
     */
    public function test_participant_can_view_chatthread()
    {
        $chatthread = Chatthread::has('chatmessages')->firstOrFail();
        $participant = $chatthread->participants[0];
        $response = $this->actingAs($participant)->ajaxJSON( 'GET', route('chatthreads.show', $chatthread->id) );
        $content = json_decode($response->content());
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'originator_id', 
                'is_tip_required', 
                'chatmessages', 
                'originator', 
                'participants', 
                'created_at', 
            ],
        ]);

        // Make sure no scheduled but undelivered messages are returned
        $num = collect($content->data->chatmessages)->reduce( function($acc, $cm) {
            return $cm->is_delivered ? $acc : ($acc+1); // expect is_delivered to be TRUE
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread which is not marked "delivered"');
    }

    /**
     *  @group chatthreads
     *  @group regression
     */
    public function test_nonparticipant_can_not_view_chatthread()
    {
        $chatthread = Chatthread::has('chatmessages')->firstOrFail();
        $nonparticipant = User::doesntHave('chatthreads')->firstOrFail();
        $response = $this->actingAs($nonparticipant)->ajaxJSON( 'GET', route('chatthreads.show', $chatthread->id) );
        $response->assertStatus(403);
    }


    /**
     *  @group chatthreads
     *  @group regression
     */
    // Test returning messages by both chathreads.show + chatmessages.index w/ filter
    public function test_can_schedule_message()
    {
        // create chat
        $originator = User::doesntHave('chatthreads')->firstOrFail();
        $otherParticipants = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->take(3)->get();
        $this->assertGreaterThan(0, $otherParticipants->count());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $otherParticipants->pluck('id')->toArray(),
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        //dd($content);
        $response->assertStatus(201);
        $chatthreadPKID = $content->data->id;

        // schedule a message for delivery in 1 day

        $now = Carbon::now();
        $tomorrow = new Carbon('tomorrow');
        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            $content->data->id, // chatthread_id
            'mcontent' => $msg,
            'deliver_at' => $tomorrow->timestamp,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.scheduleMessage', $payload) );
        $content = json_decode($response->content());
        //dd($payload, $content);
        $response->assertStatus(201);
        $scheduledMessagePKID = $content->data->id;

        $scheduledMessage = Chatmessage::find($scheduledMessagePKID);
        $this->assertNotNull($scheduledMessage);
        $this->assertFalse($scheduledMessage->is_delivered);
        $this->assertGreaterThan($now->timestamp, (new Carbon($scheduledMessage->deliver_at))->timestamp); // %TODO CHECKME
        //$this->assertEquals($payload['deliver_at']->timestamp, new Carbon($scheduledMessage->deliver_at)->timestamp); // %TODO CHECKME

        // Should not return scheduled message pre-delivery
        $response = $this->actingAs($originator)->ajaxJSON( 'GET', route('chatthreads.show', $chatthreadPKID) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $num = collect($content->data->chatmessages)->reduce( function($acc, $m) use($scheduledMessagePKID) {
            return ( $m->id !== $scheduledMessagePKID ) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Scheduled but not yet delivered message was returned by message list call');
        $num = collect($content->data->chatmessages)->reduce( function($acc, $cm) { // Make sure all messages are 'delivered'
            return $cm->is_delivered ? $acc : ($acc+1); // expect is_delivered to be TRUE
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread which is not marked "delivered"');

        // [ ] Invoking deliver does not yet deliver the message
        $chatthread = Chatthread::findOrFail($chatthreadPKID);
        $scheduledMessage->deliverScheduled();
        $chatthread->refresh();
        $scheduledMessage->refresh();
        $response = $this->actingAs($originator)->ajaxJSON( 'GET', route('chatthreads.show', $chatthreadPKID) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $num = collect($content->data->chatmessages)->reduce( function($acc, $m) use($scheduledMessagePKID) {
            return ( $m->id !== $scheduledMessagePKID ) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Scheduled but not yet delivered message was returned by message list call');

        // ] Manually change date to fake delivery date passed, re-infoke deliver, message should now be visible
        $yesterday = new Carbon('yesterday');
        $scheduledMessage->rescheduleMessage($yesterday->timestamp);
        $chatthread->refresh();
        $scheduledMessage->refresh();
        $scheduledMessage->deliverScheduled();
        $chatthread->refresh();
        $scheduledMessage->refresh();

        //$payload = [ 'take' => 100, 'chatthread_id' => $chatthreadPKID ];
        //$response = $this->actingAs($originator)->ajaxJSON( 'GET', route('chatthreads.index', $payload) );
        $response = $this->actingAs($originator)->ajaxJSON( 'GET', route('chatthreads.show', $chatthreadPKID) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);
        /*
        $num = collect($content->data)->reduce( function($acc, $m) use($scheduledMessagePKID) {
            return ( $m->id !== $scheduledMessagePKID ) ? $acc : ($acc+1);
        }, 0);
         */
        $this->assertTrue( $scheduledMessage->is_delivered );
        $this->assertTrue( collect($content->data->chatmessages)->contains('id', $scheduledMessage->id) );
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

