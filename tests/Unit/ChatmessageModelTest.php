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
     * @group OFF-regression
     * @group here0520
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
//dd($messages[0]);
        //$this->assertObjectHasAttribute('chatthread_id', $messages[0]);
        //$this->assertObjectHasAttribute('sender_id', $messages[0]);
        //$this->assertObjectHasAttribute('deliver_at', $messages[0]);
        //dd($messages->keys());
        //dd($messages->toArray());
    }

    /**
     * @group chatmessage-model
     * @group OFF-regression
     */
    public function test_can_start_chat()
    {
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

