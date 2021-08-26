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
     *  @group here0826
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
     *  @group chatmessagegroups
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_chatmessagegroups()
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

