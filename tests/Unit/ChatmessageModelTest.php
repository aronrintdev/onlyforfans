<?php
namespace Tests\Unit;

use DB;
use App;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\TestDatabaseSeeder;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

use App\Libs\FactoryHelpers;

use App\Models\User;
use App\Models\Chatthread;
use App\Models\Chatmessage;
use App\Models\Post;
use App\Models\Mediafile;
use App\Models\Diskmediafile;
use App\Enums\MediafileTypeEnum;

class ChatmessageModelTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * @group chatmessage-model
     * @group regression
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
     * @group OFF-regression
     * @group here0521
     */
    public function test_can_start_chat()
    {
        $originator = User::doesntHave('chatthreads')->firstOrFail();
        $receiver = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->firstOrFail();
        $this->assertEquals(0, $originator->chatthreads->count());
        //$this->assertEquals(0, $originator->receivedmessages->count());
        $this->assertEquals(0, $originator->sentmessages->count());
        $this->assertEquals(0, $receiver->chatthreads->count());
        //$this->assertEquals(0, $receiver->receivedmessages->count());
        $this->assertEquals(0, $receiver->sentmessages->count());

        $chatthread = Chatthread::startChat($originator);
        $this->assertNotNull($chatthread);
        $this->assertNotNull($chatthread->id);
        $this->assertEquals(1, $chatthread->participants->count());
        $this->assertNotNull($chatthread->originator);
        $this->assertEquals($originator->id, $chatthread->originator->id);

        $chatthread->addParticipant($receiver);
        $chatthread->refresh();
        $this->assertEquals(2, $chatthread->participants->count());
        $this->assertEquals(0, $chatthread->chatmessages->count());

        $chatthread->sendMessage($originator, $this->faker->realText);
        $chatthread->refresh();
        $this->assertEquals(1, $chatthread->chatmessages->count());

        //dd($images[0]->is_image);
        /*
                $chatthread = Chatthread::startChat($s)->addParticipant($r); // $s is 'originator'
                for ( $i = 0 ; $i < $msgCount ; $i++ ) {
                    $chatthread->sendMessage($r, $this->faker->realText);
                }
         */
    }

    // ------------------------------

    protected function setUp() : void
    {
        parent::setUp();

        if ( !App::environment(['testing']) ) {
            return;
        }

        $this->seed(TestDatabaseSeeder::class);
    }

    protected function tearDown() : void {
        parent::tearDown();
    }
}

