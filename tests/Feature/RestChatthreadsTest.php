<?php
namespace Tests\Feature;

use DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;

use Database\Seeders\TestDatabaseSeeder;

use App\Events\MessageSentEvent;
use App\Models\Chatmessage;
use App\Models\Chatthread;
use App\Models\Timeline;
use App\Models\User;


class RestChatthreadsTest extends TestCase
{
    use WithFaker;

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
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
     *  @group regression-base
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
     *  @group regression-base
     */
    public function test_can_list_sorted_chatthreads()
    {
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());

        // Sort by most recent
        $payload = [ 'take' => 100, 'sortBy' => 'recent' ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('chatthreads.index', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $chatthreads = collect($content->data ?? []);
        $this->assertEquals( // Check order
            $chatthreads->sortByDesc(function ($v, $k) {
                return Carbon::parse($v->updated_at)->timestamp;
            })->pluck('id'),
            $chatthreads->pluck('id')
        );

        // Sort by most oldest
        $payload = [ 'take' => 100, 'sortBy' => 'oldest' ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('chatthreads.index', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $chatthreads = collect($content->data ?? []);
        $this->assertEquals( // Check order
            $chatthreads->sortBy(function ($v, $k) {
                return Carbon::parse($v->updated_at)->timestamp;
            })->pluck('id'),
            $chatthreads->pluck('id')
        );
    }

    /**
     *  @group FIXME-chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_filtered_chatthreads()
    {
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());

        // Sort by most recent
        $payload = [ 'take' => 100, 'is_unread' => 1 ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('chatthreads.index', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $chatthreads = collect($content->data ?? []);
        $num = $chatthreads->reduce( function($acc, $ct) {
            return collect($ct->chatmessages)->reduce( function($acc, $m) {
                return ( !$m->is_read ) ? $acc : ($acc+1);
            });
        }, 0);
        $this->assertEquals(0, $num, 'Found message that was read with "unread only" filter');
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_can_get_total_unread_message_count()
    {
        $chatthread = Chatthread::whereHas('chatmessages', function($q) {
            $q->where('is_read', 0);
        })->firstOrFail();
        $participant = $chatthread->participants[0];

        $response = $this->actingAs($participant)->ajaxJSON('GET', route('chatthreads.totalUnreadCount'));
        $content = json_decode($response->content());

        $response->assertStatus(200);
        $this->assertTrue($content->total_unread_count > 0);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_can_mark_all_messages_read_for_a_user()
    {
        $chatthread = Chatthread::whereHas('chatmessages', function($q) {
            $q->where('is_read', 0);
        })->firstOrFail();
        $participant = $chatthread->participants[0];

        $response = $this->actingAs($participant)->ajaxJSON('POST', route('chatthreads.markAllRead'));

        $response->assertStatus(200);
        $this->assertTrue($chatthread->chatmessages()->where([
            ['is_read', '=', 0],
            ['sender_id', '<>', $participant->id]
        ])->count() == 0);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_can_mark_messages_read_for_a_thread()
    {
        $chatthread = Chatthread::whereHas('chatmessages', function($q) {
            $q->where('is_read', 0);
        })->firstOrFail();
        $participant = $chatthread->participants[0];

        $response = $this->actingAs($participant)->ajaxJSON('POST', route('chatthreads.markRead', $chatthread->id));

        $response->assertStatus(200);
        $this->assertTrue($chatthread->chatmessages()->where([
            ['is_read', '=', 0],
            ['sender_id', '<>', $participant->id]
        ])->count() == 0);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_can_toggle_mute_status_for_a_thread()
    {
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $chatthread = $sessionUser->chatthreads[0];

        $payload = [
            'is_muted' => true,
        ];
        $response = $this->actingAs($sessionUser)->ajaxJSON('POST', route('chatthreads.toggleMute', $chatthread->id), $payload);
        $response->assertStatus(200);
        $this->assertEquals(1, $chatthread->participants()->find($sessionUser->id)->pivot->is_muted);

        $payload = [
            'is_muted' => false,
        ];
        $response = $this->actingAs($sessionUser)->ajaxJSON('POST', route('chatthreads.toggleMute', $chatthread->id), $payload);
        $response->assertStatus(200);
        $this->assertEquals(0, $chatthread->participants()->find($sessionUser->id)->pivot->is_muted);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_can_get_mute_status_for_a_thread()
    {
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $chatthread = $sessionUser->chatthreads[0];

        $payload = [
            'is_muted' => true,
        ];
        $this->actingAs($sessionUser)->ajaxJSON('POST', route('chatthreads.toggleMute', $chatthread->id), $payload);
        $response = $this->actingAs($sessionUser)->ajaxJSON('GET', route('chatthreads.getMuteStatus', $chatthread->id));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $this->assertEquals(1, $content->is_muted);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_can_create_chat_thread_with_selected_participants()
    {
        // $originator = User::doesntHave('chatthreads')->firstOrFail();
        // $participants = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->take(3)->get();
        $originator = User::factory()->create();
        $originator = User::find($originator->id);
        $participants = new Collection([User::factory()->create(), User::factory()->create(), User::factory()->create() ]);
        $this->assertGreaterThan(0, $participants->count());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $participants->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'chatthreads' => [
                0 => [ 'id', 'originator_id', 'is_tip_required', 'created_at' ],
            ],
        ]);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_should_not_allow_chatthread_create_if_not_following_recipient()
    {
        $timeline = Timeline::has('followers','>=',1)->firstOrFail();
        $creator = $timeline->user;
        $nonfans = User::whereDoesntHave('followedtimelines', function($q1) use(&$timeline) {
            $q1->where('timelines.id', $timeline->id);
        })->where('id', '<>', $creator->id)->take(1)->get();
        $this->assertGreaterThan(0, $nonfans->count());
        //$fan = $timeline->followers[0];

        $payload = [
            'originator_id' => $creator->id,
            'participants' => $nonfans->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText,
        ];
        $response = $this->actingAs($creator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $response->assertStatus(422);
        //$content = json_decode($response->content());
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     *  @group here0906
     */
    public function test_can_create_direct_chat()
    {
        // (1) test the 'create new thread' case
        $creator = User::has('timeline.followers','>=',1)->doesntHave('chatthreads')->firstOrFail();
        $timeline = $creator->timeline;
        $fan = $timeline->followers[0];

        $this->assertEquals(0, $creator->chatthreads->count());

        $payload = [
            'originator_id' => $fan->id,
            'participant_id' => $creator->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON( 'POST', route('chatthreads.findOrCreateDirect', $payload) );
        $response->assertStatus(201);

        $creator->refresh();
        $this->assertEquals(1, $creator->chatthreads->count());
        $content = json_decode($response->content());

        // (2) test the 'get existing' case
        $payload = [
            'originator_id' => $fan->id,
            'participant_id' => $creator->id,
        ];
        $response = $this->actingAs($creator)->ajaxJSON( 'POST', route('chatthreads.findOrCreateDirect', $payload) );
        $response->assertStatus(201);
        $creator->refresh();
        $this->assertEquals(1, $creator->chatthreads->count());
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_should_create_thread_with_originator_plus_single_recipient_and_send_message()
    {
        Event::fake([
            MessageSentEvent::class,
        ]);

        // create chat
        // $originator = User::doesntHave('chatthreads')->firstOrFail();
        $originator = User::factory()->create();
        $originator = User::find($originator->id);

        // $recipients = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->take(1)->get();
        $recipients = new Collection([User::factory()->create()]);
        $this->assertGreaterThan(0, $recipients->count());

        // --- Create a chatthread ---

        $msgs = [];
        $msgs[] = $msg = $this->faker->realText;

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $recipients->pluck('id')->toArray(),
            'mcontent' => $msg,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'chatthreads' => [ // note: returns an array!
                0 => ['id', 'originator_id', 'created_at' ],
            ],
        ]);
        $this->assertEquals( $recipients->count(), count($content->chatthreads) );
        $chatthreadPKID = $content->chatthreads[0]->id; // first & only thread
        $chatthread = Chatthread::find($chatthreadPKID);
        $this->assertEquals(1, $chatthread->chatmessages->count()); // creates initial message on thread

        // send some messages to the thread

        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            $chatthread->id, // chatthread_id
            'mcontent' => $msg,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.addMessage', $payload) );
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data' => ['id', 'chatthread_id', 'mcontent', 'sender_id', 'is_delivered', 'deliver_at', 'delivered_at', 'is_read', 'is_flagged'],
        ]);
        $chatthread->refresh();
        Event::assertDispatched(MessageSentEvent::class);
        Event::assertDispatched(function (MessageSentEvent $event) use (&$chatthread) {
            return $event->chatmessage->chatthread_id === $chatthread->id;
        });
        Event::assertDispatched(function (MessageSentEvent $event) use (&$content) {
            return $event->chatmessage->id === $content->data->id;
        });

        // -

        $this->assertNotNull($chatthread);
        $this->assertEquals(2, $chatthread->chatmessages->count());
        $this->assertEquals(2, $chatthread->participants->count());
        $this->assertTrue($chatthread->participants->contains($originator->id));
        $this->assertTrue($chatthread->participants->contains($recipients[0]->id));
        $this->assertEquals($msgs[0], $chatthread->chatmessages[0]->mcontent);
        $this->assertEquals($msgs[1], $chatthread->chatmessages[1]->mcontent);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_should_create_multiple_threads_with_originator_on_all_and_single_recipient_per_and_send_message()
    {
        Event::fake([ MessageSentEvent::class, ]);
        $MAX = 3;

        // create chat
        // $originator = User::doesntHave('chatthreads')->firstOrFail();
        $originator = User::factory()->create();
        $originator = User::find($originator->id);
        $recipients = User::where('id', '<>', $originator->id)->take($MAX)->get();
        $this->assertGreaterThan(2, $recipients->count());
        $this->assertLessThanOrEqual( $MAX, $recipients->count() );
        $numberOfParticipantsExcludingOriginator = $recipients->count();

        // --- Create a chatthread (send message #1) ---

        $msgs = [];
        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            'originator_id' => $originator->id,
            'participants' => $recipients->pluck('id')->toArray(),
            'mcontent' => $msg,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'chatthreads' => [ // note: returns an array!
                0 => ['id', 'originator_id', 'created_at' ],
            ],
        ]);
        $response->assertStatus(201);
        $this->assertEquals( $numberOfParticipantsExcludingOriginator, count($content->chatthreads) ); // check # of threads created

        $chatthreads = Chatthread::whereIn('id', collect($content->chatthreads)->pluck('id'))->get();
        //dd($chatthreads[0]->chatmessages);
        $this->assertTrue( $chatthreads->contains($content->chatthreads[0]->id) ); // sanity check
        if ( count($content->chatthreads) > 1 ) {
            $this->assertTrue( $chatthreads->contains($content->chatthreads[1]->id) ); // sanity check
        }
        if ( count($content->chatthreads) > 2 ) {
            $this->assertTrue( $chatthreads->contains($content->chatthreads[2]->id) ); // sanity check
        }
        $this->assertNotNull($chatthreads[0]);
        $this->assertNotNull($chatthreads[0]->id);
        $this->assertNotNull($chatthreads[0]->chatmessages);
        $this->assertNotNull($chatthreads[0]->chatmessages[0]);
        $this->assertEquals($msgs[0], $chatthreads[0]->chatmessages[0]->mcontent);

        // --- Send messages ---

        // - Chatthread [0]
        $thisCT = $chatthreads[0];
        $in = $recipients->filter( function($r) use(&$thisCT) { // orig. recipients who should be on this thread
            return $thisCT->participants->contains($r->id);
        })->values();
        $this->assertEquals(1, $in->count() );

        $out = $recipients->filter( function($r) use(&$thisCT) { // orig. recipients who should not be on this thread
            return !$thisCT->participants->contains($r->id);
        })->values();
        //dd($out);
        $this->assertEquals(2, $out->count() );

        // Send message #2
        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            $thisCT->id, // chatthread_id
            'mcontent' => $msg,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.addMessage', $payload) );
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $thisCT->refresh();
//dd($content);
        $response->assertJsonStructure([
            'data' => ['id', 'chatthread_id', 'mcontent', 'sender_id', 'is_delivered', 'deliver_at', 'delivered_at', 'is_read', 'is_flagged'],
        ]);

        // Check event dispatching
        Event::assertDispatched(MessageSentEvent::class);
        Event::assertDispatched(function (MessageSentEvent $event) use (&$thisCT) {
            //dd($event->chatmessage->toArray(), $chatthread->toArray());
            return $event->chatmessage->chatthread_id === $thisCT->id;
        });
        Event::assertDispatched(function (MessageSentEvent $event) use (&$content) {
            return $event->chatmessage->id === $content->data->id;
        });

        // Send message #3
        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            $thisCT->id, // chatthread_id
            'mcontent' => $msg,
        ];
        $response = $this->actingAs($in[0])->ajaxJSON( 'POST', route('chatthreads.addMessage', $payload) );
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $thisCT->refresh();

        // Check event dispatching
        Event::assertDispatched(MessageSentEvent::class);
        Event::assertDispatched(function (MessageSentEvent $event) use (&$thisCT) {
            return $event->chatmessage->chatthread_id === $thisCT->id;
        });
        Event::assertDispatched(function (MessageSentEvent $event) use (&$content) {
            return $event->chatmessage->id === $content->data->id;
        });

        $this->assertEquals(3, $thisCT->chatmessages->count());
        $this->assertEquals(2, $thisCT->participants->count());
        $this->assertTrue($thisCT->participants->contains($originator->id));
        $this->assertTrue($thisCT->participants->contains($in[0]->id));
        $this->assertFalse($thisCT->participants->contains($out[0]->id));
        $this->assertFalse($thisCT->participants->contains($out[1]->id));

        /*
dd('msgs', $msgs, 'chatmessages.mcontent',
    $thisCT->chatmessages[0]->mcontent,
    $thisCT->chatmessages[1]->mcontent,
    $thisCT->chatmessages[2]->mcontent
);
         */

        $this->assertEquals($msgs[0], $thisCT->chatmessages[0]->mcontent);
        $this->assertEquals($msgs[1], $thisCT->chatmessages[1]->mcontent);
        $this->assertEquals($msgs[2], $thisCT->chatmessages[2]->mcontent);

    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_should_create_thread_with_included_first_message_content()
    {
        Event::fake([
            MessageSentEvent::class,
        ]);

        // create chat
        // $originator = User::doesntHave('chatthreads')->firstOrFail();
        $originator = User::factory()->create();
        $originator = User::find($originator->id);

        // $recipients = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->take(1)->get();
        $recipients = new Collection([ User::factory()->create() ]);
        $this->assertGreaterThan(0, $recipients->count());

        // --- Create a chatthread ---

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $recipients->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText, // send content, will create initial message for thread
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'chatthreads' => [ // note: returns an array!
                0 => ['id', 'originator_id', 'created_at' ],
            ],
        ]);
        $response->assertStatus(201);
        $this->assertEquals( $recipients->count(), count($content->chatthreads) );
        $chatthreadPKID = $content->chatthreads[0]->id; // first & only thread
        $chatthread = Chatthread::find($chatthreadPKID);
        $this->assertNotNull($chatthread);
        $this->assertEquals($payload['mcontent'], $chatthread->chatmessages[0]->mcontent);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    public function test_should_create_thread_with_included_first_message_content_pre_scheduled()
    {
        Event::fake([
            MessageSentEvent::class,
        ]);

        // create chat
        // $originator = User::doesntHave('chatthreads')->firstOrFail();
        $originator = User::factory()->create();
        $originator = User::find($originator->id);

        // $recipients = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->take(1)->get();
        $recipients = new Collection([ User::factory()->create() ]);
        $this->assertGreaterThan(0, $recipients->count());

        // --- Create a chatthread ---

        $now = Carbon::now();
        $tomorrow = new Carbon('tomorrow');
        $payload = [
            'originator_id' => $originator->id,
            'participants' => $recipients->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText, // send content, will create initial message for thread
            'deliver_at' => $tomorrow->timestamp,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'chatthreads' => [ // note: returns an array!
                0 => ['id', 'originator_id', 'created_at' ],
            ],
        ]);
        $response->assertStatus(201);
        $this->assertEquals( $recipients->count(), count($content->chatthreads) );
        $chatthreadPKID = $content->chatthreads[0]->id; // first & only thread
        $chatthread = Chatthread::find($chatthreadPKID);
        $this->assertNotNull($chatthread);
        $this->assertEquals(0, $chatthread->chatmessages->count()); // should not be visible yet

        $scheduledMsg = Chatmessage::where('chatthread_id', $chatthreadPKID)->first();
        $this->assertNotNull($scheduledMsg);
        $this->assertEquals($payload['mcontent'], $scheduledMsg->mcontent);
    }

    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
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
     *  @group regression-base
     */
    public function test_nonparticipant_can_not_view_chatthread()
    {
        $chatthread = Chatthread::has('chatmessages')->firstOrFail();
        // $nonparticipant = User::doesntHave('chatthreads')->firstOrFail();
        $nonparticipant = User::factory()->create();
        $nonparticipant = User::find($nonparticipant->id);
        $response = $this->actingAs($nonparticipant)->ajaxJSON( 'GET', route('chatthreads.show', $chatthread->id) );
        $response->assertStatus(403);
    }


    /**
     *  @group chatthreads
     *  @group regression
     *  @group regression-base
     */
    // Test returning messages by both chathreads.show + chatmessages.index w/ filter
    public function test_can_schedule_message()
    {
        // create chat
        // $originator = User::doesntHave('chatthreads')->firstOrFail();
        // $recipients = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->take(3)->get();
        $originator = User::factory()->create();
        $originator = User::find($originator->id);
        $recipients = new Collection([User::factory()->create(), User::factory()->create(), User::factory()->create()]);
        $this->assertGreaterThan(0, $recipients->count());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $recipients->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        //dd($content);
        $response->assertStatus(201);
        $chatthreadPKID = $content->chatthreads[0]->id;

        // schedule a message for delivery in 1 day

        $now = Carbon::now();
        $tomorrow = new Carbon('tomorrow');
        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            $chatthreadPKID,
            'mcontent' => $msg,
            'deliver_at' => $tomorrow->timestamp,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.addMessage', $payload) );
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

        Event::fake([MessageSentEvent::class]);
        $scheduledMessage->deliver();
        Event::assertNotDispatched(MessageSentEvent::class);

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
        $scheduledMessage->schedule($yesterday->timestamp);
        $chatthread->refresh();
        $scheduledMessage->refresh();

        Event::fake([MessageSentEvent::class]);
        $scheduledMessage->deliver();
        Event::assertDispatched(MessageSentEvent::class);

        $chatthread->refresh();
        $scheduledMessage->refresh();

        //$payload = [ 'take' => 100, 'chatthread_id' => $chatthreadPKID ];
        //$response = $this->actingAs($originator)->ajaxJSON( 'GET', route('chatthreads.index', $payload) );

        // Note: Message is latest delivered which may not be this message due to seeder.
        // $response = $this->actingAs($originator)->ajaxJSON( 'GET', route('chatthreads.show', $chatthreadPKID) );
        // $response->assertStatus(200);
        // $content = json_decode($response->content());
        // dd($content, $scheduledMessage->id);

        /*
        $num = collect($content->data)->reduce( function($acc, $m) use($scheduledMessagePKID) {
            return ( $m->id !== $scheduledMessagePKID ) ? $acc : ($acc+1);
        }, 0);
         */
        $this->assertTrue( $scheduledMessage->is_delivered );
        // $this->assertTrue( collect($content->data->chatmessages)->contains('id', '=', $scheduledMessage->id) );
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

