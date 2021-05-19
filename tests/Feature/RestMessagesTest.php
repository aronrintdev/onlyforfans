<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Support\Facades\Config;

class RestMessagesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group messages
     *  @group regression
     *  @group here0519
     */
    public function test_can_list_messages()
    {
        $chatthread = ChatThread::first();
        //$this->seed(\Database\Seeders\TestDatabaseSeeder::class);
        $timeline = Timeline::has('posts','>=',1)->first(); 
        $creator = $timeline->user;
        $expectedCount = Post::where('postable_type', 'timelines')
            ->where('postable_id', $timeline->id)
            ->count();

        $response = $this->actingAs($creator)->ajaxJSON('GET', route('chat-messages.index'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
        $this->assertEquals(1, $content->meta->current_page);
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        $this->assertEquals($expectedCount, count($content->data));
        $this->assertEquals($timeline->posts->count(), count($content->data));
        $this->assertObjectHasAttribute('postable_id', $content->data[0]);
        $this->assertEquals($timeline->id, $content->data[0]->postable_id);
        $this->assertObjectHasAttribute('postable_type', $content->data[0]);
        $this->assertEquals('timelines', $content->data[0]->postable_type);
        $this->assertObjectHasAttribute('description', $content->data[0]);
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
POST          | chat-messages                                | chat-messages.store                | App\Http\Controllers\MessageController@store
GET|HEAD      | chat-messages                                | chat-messages.index                | App\Http\Controllers\MessageController@index
GET|HEAD      | chat-messages/contacts                       | messages.fetchcontacts             | App\Http\Controllers\MessageController@fetchContacts
POST          | chat-messages/mark-all-as-read               | messages.markallasread             | App\Http\Controllers\MessageController@markAllAsRead
GET|HEAD      | chat-messages/scheduled                      | messages.fetchscheduled            | App\Http\Controllers\MessageController@fetchScheduled
DELETE        | chat-messages/scheduled/{threadId}           | messages.removeschedule            | App\Http\Controllers\MessageController@removeScheduleThread
PATCH         | chat-messages/scheduled/{threadId}           | messages.editschedule              | App\Http\Controllers\MessageController@editScheduleThread
GET|HEAD      | chat-messages/users                          | messages.fetchusers                | App\Http\Controllers\MessageController@fetchUsers
GET|HEAD      | chat-messages/{id}                           | messages.fetchcontact              | App\Http\Controllers\MessageController@fetchcontact
DELETE        | chat-messages/{id}                           | messages.clearcontact              | App\Http\Controllers\MessageController@clearUser
POST          | chat-messages/{id}/custom-name               | messages.customname                | App\Http\Controllers\MessageController@setCustomName
POST          | chat-messages/{id}/mark-as-read              | messages.markasread                | App\Http\Controllers\MessageController@markAsRead
GET|HEAD      | chat-messages/{id}/mediafiles                | messages.mediafiles                | App\Http\Controllers\MessageController@listMediafiles
PATCH         | chat-messages/{id}/mute                      | messages.mute                      | App\Http\Controllers\MessageController@mute
GET|HEAD      | chat-messages/{id}/search                    | messages.filtermessages            | App\Http\Controllers\MessageController@filterMessages
DELETE        | chat-messages/{id}/threads/{threadId}        | messages.removethread              | App\Http\Controllers\MessageController@removeThread
POST          | chat-messages/{id}/threads/{threadId}/like   | messages.setlike                   | App\Http\Controllers\MessageController@setLike
POST          | chat-messages/{id}/threads/{threadId}/unlike | messages.setunlike                 | App\Http\Controllers\MessageController@setUnlike
PATCH         | chat-messages/{id}/unmute                    | messages.unmute                    | App\Http\Controllers\MessageController@unmute
GET|HEAD      | unread-messages-count                        | messages.unreadmessagescount       | App\Http\Controllers\MessageController@getUnreadMessagesCount
*/
