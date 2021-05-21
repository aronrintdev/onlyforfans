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

class RestChatthreadsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     *  @group chatthreads
     *  @group OFF-regression
     */
    public function test_can_index_chatthreads()
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
     *  @group OFF-regression
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
     *  @group OFF-regression
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

        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            $content->data->id, // chatthread_id
            'mcontents' => $msg,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.sendMessage', $payload) );
        $response->assertStatus(201);

        $msgs[] = $msg = $this->faker->realText;
        $payload = [
            $content->data->id, // chatthread_id
            'mcontents' => $msg,
        ];
        $response = $this->actingAs($otherParticipants[0])->ajaxJSON( 'POST', route('chatthreads.sendMessage', $payload) );
        $response->assertStatus(201);

        // --
        $chatthread = Chatthread::find($chatthreadPKID);
        $this->assertNotNull($chatthread);
        $this->assertEquals(2, $chatthread->chatmessages->count());
        $this->assertEquals((1+$otherParticipants->count()), $chatthread->participants->count());
        $this->assertTrue($chatthread->participants->contains($originator->id));
        $this->assertTrue($chatthread->participants->contains($otherParticipants[0]->id)); // %TODO reduce op
        // [ ] check message contents w/ reduce


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

