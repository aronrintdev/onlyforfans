<?php
namespace Tests\Unit;

use Carbon\Carbon;
use DB;
use App;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\TestDatabaseSeeder;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

use App\Libs\FactoryHelpers;

use App\Models\User;
use App\Models\Chatthread;
use App\Models\Chatmessage;
use App\Models\Post;
use App\Models\Mycontact;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Enums\MediafileTypeEnum;

class ChatmessageModelTest extends TestCase
{
    use WithFaker;

    /**
     * @group chatmessage-model
     * @group regression
     * @group regression-base
     */
    public function test_should_get_chatmessages()
    {
        $messages = Chatmessage::get();
        //$this->assertObjectHasAttribute('description', $content->data); // can see contents
        $this->assertGreaterThan(0, $messages->count());
        $this->assertNotNull($messages[0]);
        $this->assertNotNull($messages[0]->mcontent);
        $this->assertNotNull($messages[0]->chatthread_id);
        $this->assertNotNull($messages[0]->sender_id);

        $this->assertNotNull($messages[0]->sender);
        $this->assertNotNull($messages[0]->sender->id);

        $this->assertNotNull($messages[0]->chatthread);
        $this->assertNotNull($messages[0]->chatthread->id);
    }

    /**
     * @group chatmessage-model
     * @group regression
     * @group regression-base
     */
    public function test_can_start_chat()
    {
        $originator = User::doesntHave('chatthreads')->firstOrFail();
        $receiver = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->firstOrFail();
        $this->assertEquals(0, $originator->chatthreads->count());
        $this->assertEquals(0, $originator->sentmessages->count());
        $this->assertEquals(0, $receiver->chatthreads->count());
        $this->assertEquals(0, $receiver->sentmessages->count());

        $chatthread = Chatthread::startChat($originator);
        $this->assertNotNull($chatthread);
        $this->assertNotNull($chatthread->id);
        $this->assertEquals(1, $chatthread->participants->count());
        $this->assertNotNull($chatthread->originator);
        $this->assertEquals($originator->id, $chatthread->originator->id);

        $chatthread->addParticipant($receiver->id);
        $chatthread->refresh();
        $this->assertEquals(2, $chatthread->participants->count());
        $this->assertEquals(0, $chatthread->chatmessages->count());

        $msgs = [];

        // send 1st message
        $msgs[] = $msg = $this->faker->realText;
        $chatthread->sendMessage($originator, $msg);
        $chatthread->refresh();
        $this->assertEquals(1, $chatthread->chatmessages->count());

        // send 2nd message
        $msgs[] = $msg = $this->faker->realText;
        $chatthread->sendMessage($originator, $msg);
        $chatthread->refresh();
        $originator->refresh();
        $receiver->refresh();
        $this->assertEquals(2, $chatthread->chatmessages->count());

        $this->assertEquals(2, $originator->sentmessages->count());
        $this->assertEquals(0, $receiver->sentmessages->count());
        $this->assertEquals(2, $receiver->chatthreads()->firstWhere('chatthread_id', $chatthread->id)->chatmessages->count());
        $this->assertEquals($msgs[0], $receiver->chatthreads()->firstWhere('chatthread_id', $chatthread->id)->chatmessages[0]->mcontent);
        $this->assertEquals($msgs[1], $receiver->chatthreads()->firstWhere('chatthread_id', $chatthread->id)->chatmessages[1]->mcontent);

    }

    /**
     * @group chatmessage-model
     * @group regression
     * @group regression-base
     */
    public function test_can_schedule_message()
    {
        $originator = User::doesntHave('chatthreads')->firstOrFail();
        $receiver = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->firstOrFail();

        $chatthread = Chatthread::startChat($originator);
        $this->assertNotNull($chatthread);

        $chatthread->addParticipant($receiver->id);
        $chatthread->refresh();

        $msgs = [];

        // schedule 1st message
        $now = Carbon::now();
        $tomorrow = new Carbon('tomorrow');
        $msgs[] = $msg = $this->faker->realText;
        $chatthread->scheduleMessage($originator, $msg, $tomorrow->timestamp);
        $chatthread->refresh();

        $this->assertEquals(0, $chatthread->chatmessages->count()); // shouldn't be visible

        $chatmessages = Chatmessage::where('chatthread_id', $chatthread->id)->get();
        $this->assertEquals(1, $chatmessages->count());
        $this->assertFalse($chatmessages[0]->is_delivered);
        $this->assertEquals($tomorrow, $chatmessages[0]->deliver_at);
    }

    /**
     * @group chatmessage-model
     * @group mycontacts
     * @group regression
     * @group regression-base
     * @group here0607
     */
    public function test_should_get_mycontacts()
    {
        $mycontacts = Mycontact::get();
        $this->assertGreaterThan(0, $mycontacts->count());
        $this->assertNotNull($mycontacts[0]);
        $this->assertNotNull($mycontacts[0]->contact_id);
        $this->assertNotNull($mycontacts[0]->owner_id);

        $this->assertNotNull($mycontacts[0]->owner);
        $this->assertNotNull($mycontacts[0]->owner->id);

        $this->assertNotNull($mycontacts[0]->contact);
        $this->assertNotNull($mycontacts[0]->contact->id);
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();

        if ( !App::environment(['testing']) ) {
            return;
        }

        //$this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

