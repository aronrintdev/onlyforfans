<?php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;

use App\Models\User;
use App\Models\Chatthread;
use App\Models\Chatmessage;

class RestChatmessagesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group chatmessages
     *  @group OFF-regression
     *  @group here0521
     */
    public function test_can_index_chatmessages()
    {
        //$originator = User::doesntHave('chatthreads')->firstOrFail();
        //$receiver = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->firstOrFail();
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
                    'is_read', 
                    'is_flagged', 
                ],
            ],
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        //dd($content);
        //dd($content->messages);

        // Check no messages from threads in which I am not a participant
        $chatmessages = collect($content->data);
        $num = $chatmessages->reduce( function($acc, $cm) use(&$sessionUser) {
            $chatmessage = Chatmessage::find($cm->id);
            $this->assertNotNull($chatmessage);
            return ($chatmessage->chatthread->participants->contains($sessionUser->id)) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread in which session user is not a participant (of '.$content->meta->total.' total messages)');
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

