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
        $messages = Chatmessages::get();
        dd($messages);
    }

    /**
     * @group chatmessage-model
     * @group OFF-regression
     */
    public function test_should_start_chat()
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

