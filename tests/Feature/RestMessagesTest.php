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
use App\Models\ChatThread;
use App\Models\Message;

class RestMessagesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group messages
     *  @group DEPRECATED-regression
     *  @group OFF-here0519
     */
    public function test_can_fetch_contacts()
    {
        //$receiver = User::has('chatthreads.messages','>=', 1)->firstOrFail();
        $receiver = User::has('chatthreads.messages','>=', 1)->has('timeline')->firstOrFail();
        //$chatthread = $receiver->chatthread;
        $payload = [ ];
        $response = $this->actingAs($receiver)->ajaxJSON( 'GET', route('messages.fetchcontacts', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            0 => [ 
                'last_message' => [
                    'messagable_id',
                    'mcounter',
                    'mcontent',
                    'sender_id',
                    'receiver_id',
                    'hasMediafile',
                    'mediafile',
                ], 
                'profile' => [
                    'slug',
                    'user_id',
                    'username',
                    'user' => [
                        'username',
                        'avatar',
                        'cover',
                    ],
                ], 
            ],
        ]);
        //dd($content);
        //dd($content->messages);
    }

    /**
     *  @group messages
     *  @group DEPRECATED-regression
     *  @group OFF-here0519
     */
    public function test_can_fetch_messages_from_single_contact()
    {
        $sessionUser = User::has('chatthreads.messages','>=', 1)->has('timeline')->firstOrFail();
        $payload = [ ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('messages.fetchcontacts', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            0 => [ 
                'last_message',
                'profile',
            ],
        ]);

        $receiver = User::find($content[0]->profile->user_id)->makeVisible('timeline');
        $this->assertNotNull($receiver);

        $payload = [
            $receiver->id,
            'offset' => 0,
            'limit' => 10,
        ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('messages.fetchcontact', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'messages' => [
                0 => [
                    'sender_id',
                    'receiver_id',
                    'schedule_datetime',
                    'messages' => [
                        0 => [
                            'mcontent',
                            'mcounter',
                            'messagable_id',
                            'mediafile',
                        ],
                    ],
                    'receiver' => [
                        'username',
                        'avatar',
                    ],
                ],
            ], 
        ]);

        //dd($content);
        //dd($content->messages);
        //dd($content);
        //dd($content->messages);
    }

    /**
     *  @group messages
     *  @group DEPRECATED-regression
     */
    public function test_can_send_chat_message()
    {
        $sessionUser = User::has('timeline')->doesntHave('chatthreads')->firstOrFail();
        $contact = User::has('timeline')->doesntHave('chatthreads')->where('id', '<>', $sessionUser->id)->firstOrFail();

        $payload = [ 'message' => $this->faker->realText, 'user_id' => $contact->id ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('chat-messages.store', $payload) ); // 1st message su -> contact
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'message' => [
                'receiver_id',
                'sender_id',
                'schedule_datetime',
                'messages' => [
                    0 => [
                        'mcontent',
                        'mcounter',
                        'messagable_id',
                    ],
                ],
            ],
        ]);
        /*
            pascale.hegmann 
            mark.kihn 
         */

        $payload = [ 'message' => $this->faker->realText, 'user_id' => $contact->id ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('chat-messages.store', $payload) ); // 2nd message su -> contact
        $response->assertStatus(200);

        $payload = [ 'message' => $this->faker->realText, 'user_id' => $sessionUser->id ];
        $response = $this->actingAs($contact)->ajaxJSON( 'POST', route('chat-messages.store', $payload) ); // 1st message contact -> su
        $response->assertStatus(200);

        $payload = [ 'message' => $this->faker->realText, 'user_id' => $contact->id ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'POST', route('chat-messages.store', $payload) ); // 4th message su -> contact
        $response->assertStatus(200);

        $cts = ChatThread::with(['receiver', 'messages'])
            ->where(function($q1) use(&$sessionUser, $contact) {
                $q1->where('sender_id', $sessionUser->id)->where('receiver_id', $contact->id);
            })
            ->orWhere(function($q2) use(&$sessionUser, $contact) {
                $q2->where('receiver_id', $sessionUser->id)->where('sender_id', $contact->id);
            })->get();

        // dd(
        //     $cts->toArray()
        //     //$cts[0]->messages->toArray(),
        // );
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

/*
[ ] GET|HEAD      | chat-messages                                | chat-messages.index                | App\Http\Controllers\MessageController@index
[/] GET|HEAD      | chat-messages/contacts                       | messages.fetchcontacts             | App\Http\Controllers\MessageController@fetchContacts
[ ] GET|HEAD      | chat-messages/scheduled                      | messages.fetchscheduled            | App\Http\Controllers\MessageController@fetchScheduled
[ ] GET|HEAD      | chat-messages/users                          | messages.fetchusers                | App\Http\Controllers\MessageController@fetchUsers
[/] GET|HEAD      | chat-messages/{id}                           | messages.fetchcontact              | App\Http\Controllers\MessageController@fetchcontact
[ ] GET|HEAD      | chat-messages/{id}/mediafiles                | messages.mediafiles                | App\Http\Controllers\MessageController@listMediafiles
[ ] GET|HEAD      | chat-messages/{id}/search                    | messages.filtermessages            | App\Http\Controllers\MessageController@filterMessages

[ ] POST          | chat-messages                                | chat-messages.store                | App\Http\Controllers\MessageController@store
[ ] POST          | chat-messages/mark-all-as-read               | messages.markallasread             | App\Http\Controllers\MessageController@markAllAsRead
[ ] DELETE        | chat-messages/scheduled/{threadId}           | messages.removeschedule            | App\Http\Controllers\MessageController@removeScheduleThread
[ ] PATCH         | chat-messages/scheduled/{threadId}           | messages.editschedule              | App\Http\Controllers\MessageController@editScheduleThread
[ ] DELETE        | chat-messages/{id}                           | messages.clearcontact              | App\Http\Controllers\MessageController@clearUser
[ ] POST          | chat-messages/{id}/custom-name               | messages.customname                | App\Http\Controllers\MessageController@setCustomName
[ ] POST          | chat-messages/{id}/mark-as-read              | messages.markasread                | App\Http\Controllers\MessageController@markAsRead
[ ] PATCH         | chat-messages/{id}/mute                      | messages.mute                      | App\Http\Controllers\MessageController@mute
[ ] DELETE        | chat-messages/{id}/threads/{threadId}        | messages.removethread              | App\Http\Controllers\MessageController@removeThread
[ ] POST          | chat-messages/{id}/threads/{threadId}/like   | messages.setlike                   | App\Http\Controllers\MessageController@setLike
[ ] POST          | chat-messages/{id}/threads/{threadId}/unlike | messages.setunlike                 | App\Http\Controllers\MessageController@setUnlike
[ ] PATCH         | chat-messages/{id}/unmute                    | messages.unmute                    | App\Http\Controllers\MessageController@unmute

        $receiver = User::has('chatthreads.messages','>=', 1)->firstOrFail();
        $chatthread = $receiver->chatthread;
        //$response = $this->actingAs($receiver)->ajaxJSON('GET', route('chat-messages.index'));
        $payload = [
            $receiver->id,
            'offset' => 0,
            'limit' => 10,
        ];
        $response = $this->actingAs($receiver)->ajaxJSON( 'GET', route('messages.fetchcontact', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        dd($content);
        dd($content->messages);
 */
