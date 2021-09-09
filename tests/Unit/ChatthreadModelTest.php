<?php
namespace Tests\Unit;

use Carbon\Carbon;
use DB;
use App;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
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

class ChatthreadModelTest extends TestCase
{
    use WithFaker;

    /**
     *  @group chatmessage-model
     *  @group regression
     *  @group regression-base
     */
    public function test_can_create_direct_chat()
    {
        $originator = User::doesntHave('chatthreads')->firstOrFail();
        $receiver = User::doesntHave('chatthreads')->where('id', '<>', $originator->id)->firstOrFail();
        $this->assertEquals(0, $originator->chatthreads->count());
        $this->assertEquals(0, $originator->sentmessages->count());
        $this->assertEquals(0, $receiver->chatthreads->count());
        $this->assertEquals(0, $receiver->sentmessages->count());

        $chatthread = Chatthread::findOrCreateDirectChat($originator, $receiver);
        $this->assertNotNull($chatthread);
        $this->assertNotNull($chatthread->id);
        $this->assertNotNull($chatthread->originator);
        $this->assertEquals($originator->id, $chatthread->originator->id);

        $this->assertEquals(2, $chatthread->participants->count());
        $this->assertTrue($chatthread->participants->contains($originator->id));
        $this->assertTrue($chatthread->participants->contains($receiver->id));
    }

    /**
     *  @group chatmessage-model
     *  @group regression
     *  @group regression-base
     *  @group erik
     */
    public function test_can_not_create_direct_chat_with_myself()
    {
        $originator = User::doesntHave('chatthreads')->firstOrFail();
        $receiver = $originator;
        $this->assertEquals(0, $originator->chatthreads->count());
        $this->assertEquals(0, $originator->sentmessages->count());

        $chatthread = Chatthread::findOrCreateDirectChat($originator, $receiver);
        $this->assertNull($chatthread);
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

