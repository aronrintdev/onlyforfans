<?php
namespace Tests\Feature;

use DB;
use Carbon\Carbon;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

use Illuminate\Foundation\Testing\WithFaker;

use Database\Seeders\TestDatabaseSeeder;
use Tests\TestCase;

use App\Events\ItemPurchased;
use App\Events\PurchaseFailed;
use App\Events\MessageSentEvent;

use App\Notifications\MessageReceived;

use App\Models\Chatmessage;
use App\Models\Chatmessagegroup;
use App\Models\Chatthread;
use App\Models\Mediafile;
use App\Models\User;
use App\Models\Timeline;
use App\Models\Vault;
use App\Models\Vaultfolder;

use App\Enums\MediafileTypeEnum;
use App\Enums\Financial\AccountTypeEnum;

class RestChatmessagegroupsTest extends TestCase
{
    use WithFaker;

    /**
     *  @group chatmessagegroups
     *  @group regression
     *  @group regression-base
     */
    public function test_can_send_mass_message()
    {
        NotificationFacade::fake();

        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
        $originator = $timeline->user;
        $fans = $timeline->followers;

        $this->assertGreaterThan(0, $fans->count());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $fans->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);

        $response->assertJsonStructure([
            'chatmessagegroup' => [ 'id', 'mgtype', 'sender_id' ],
            'chatthreads' => [
                0 => [ 'id', 'originator_id', 'is_tip_required', 'created_at' ],
            ],
            'chatmessages' => [ 
                0 => [ 'id', 'chatthread_id', 'mcontent', 'sender_id', 'deliver_at', 'is_delivered', 'is_read', 'is_flagged' ],
            ],
        ]);

        $cmg = Chatmessagegroup::find($content->chatmessagegroup->id);
        $this->assertNotNull($cmg);
        $this->assertNotNull($cmg->id);
        $this->assertNotNull($cmg->chatmessages);
        $this->assertEquals($fans->count(), $cmg->chatmessages->count());

        $this->assertEquals($fans->count(), count($content->chatthreads));

        $ct = Chatthread::find($content->chatthreads[0]->id);
        //dd($ct->chatmessages);
        $this->assertNotNull($ct);
        $this->assertNotNull($ct->id);
        $this->assertEquals(2, $ct->participants->count()); // 2 participants *per thread*, one thread per fan

        NotificationFacade::assertSentTo( $fans, MessageReceived::class );

        //$this->assertTrue( $ct->chatmessages->contains($content->chatthreads) );

        // This will *not* necessarily be true, because we made threads 'singletons', it's 
        // possible the originator may not be the one pushing the mass message (ie it may be sent
        // as a response to a fan who originated the 1-to-1 chat
        //$this->assertEquals($originator->id, $ct->originator->id);

        // Because chatthreads.store returns an array of threads (not the created messages), in addition
        // to the above, it doesn't seem like we can locate the exact message created by the 
        // chatthreads.store call above (?)
    }

    /**
     *  @group chatmessagegroups
     *  @group regression
     *  @group regression-base
     */
    public function test_can_unsend_mass_message()
    {
        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
        $originator = $timeline->user;
        $fans = $timeline->followers;

        $this->assertGreaterThan(0, $fans->count());

        // --- Send the mass message ---

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $fans->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);

        $msgCountAfterStore = Chatmessage::count();

        $response->assertJsonStructure([
            'chatmessagegroup' => [ 'id', 'mgtype', 'sender_id' ],
            'chatthreads' => [
                0 => [ 'id', 'originator_id', 'is_tip_required', 'created_at' ],
            ],
            'chatmessages' => [ 
                0 => [ 'id', 'chatthread_id', 'mcontent', 'sender_id', 'deliver_at', 'is_delivered', 'is_read', 'is_flagged' ],
            ],
        ]);

        $cmg = Chatmessagegroup::find($content->chatmessagegroup->id);
        $this->assertNotNull($cmg);
        $this->assertNotNull($cmg->id);

        $this->assertEquals($fans->count(), count($content->chatthreads));

        $ct = Chatthread::find($content->chatthreads[0]->id);
        $this->assertNotNull($ct);
        $this->assertNotNull($ct->id);

        // --- Mark 1 message (thread) as 'read' (past tense) by a fan/receiver ---

        $expectedDeletedCount = $fans->count();

        $response = $this->actingAs($fans[0])->ajaxJSON('POST', route('chatthreads.markRead', $ct->id));
        $response->assertStatus(200);
        $expectedDeletedCount -= 1; // won't delete read message

        // --- Unsend the unread messages ---

        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatmessagegroups.unsend', $cmg->id) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data' => [ 'id', 'mgtype', 'sender_id' ],
        ]);
        $this->assertEquals($cmg->id, $content->data->id);

        $msgCountAfterUnsend = Chatmessage::query()->count();
        $this->assertLessThan($msgCountAfterStore, $msgCountAfterUnsend); // should be less after unsending
        $this->assertEquals($msgCountAfterStore-$expectedDeletedCount, $msgCountAfterUnsend); // should account for unread message
    }

    /**
     *  @group chatmessagegroups
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_chatmessagegroups()
    {
        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
        $originator = $timeline->user;
        $fans = $timeline->followers;

        $this->assertGreaterThan(0, $fans->count());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => $fans->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);

        $ctR = Chatthread::find($content->chatthreads[0]->id);
        $this->assertNotNull($ctR->id);

        $payload = [];
        $response = $this->actingAs($originator)->ajaxJSON( 'GET', route('chatmessagegroups.index', $payload) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);
    }

    /**
     *  @group chatmessagegroups
     *  @group regression
     *  @group regression-base
     *  @group OFF-here0923
     */
    public function test_can_analyze_mass_message_stats()
    {
        Storage::fake('s3');
        $this->assertTrue(Config::get('segpay.fake'), 'Your SEGPAY_FAKE .env variable is not set to true.');

        $timeline = Timeline::has('followers', '>=', 3)->firstOrFail();
        $originator = $timeline->user; // aka creator
        $fans = $timeline->followers; // aka receivers of mass message

        $this->assertGreaterThan(0, $fans->count());

        // -------------------------------------------------------------------------------------------
        // Need to simulate what Create Chatmessage form does, which is to upload mediafiles first
        // to vault, *then* store the [chatmessages] record with the attachments passed in POST payload
        // -------------------------------------------------------------------------------------------

        $filenames = [
            $this->faker->slug,
            $this->faker->slug,
        ];
        $files = [
            UploadedFile::fake()->image($filenames[0], 200, 200),
            UploadedFile::fake()->image($filenames[1], 200, 200),
        ];

        $primaryVault = Vault::primary($originator)->first();
        $vf = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        $attachments = [];

        // upload 1st file
        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $files[0],
            'resource_type' => 'vaultfolders',
            'resource_id' => $vf->id,
        ];
        $response = $this->actingAs($originator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $mf = Mediafile::find($content->mediafile->id);
        if ($mf) {
            $attachments[] = $mf->toArray();
        }

        // upload 2nd file
        $payload = [
            'mftype' => MediafileTypeEnum::VAULT,
            'mediafile' => $files[1],
            'resource_type' => 'vaultfolders',
            'resource_id' => $vf->id,
        ];
        $response = $this->actingAs($originator)->ajaxJSON('POST', route('mediafiles.store'), $payload);
        $response->assertStatus(201);
        $content = json_decode($response->content());
        $mf = Mediafile::find($content->mediafile->id);
        if ($mf) {
            $attachments[] = $mf->toArray();
        }

        $this->assertGreaterThan(0, count($attachments));

        // --- Send the Group Chat Message ---

        $cmgCountBefore = Chatmessagegroup::count();
        $payload = [
            'originator_id' => $originator->id,
            'participants' => $fans->pluck('id')->toArray(),
            'mcontent' => $this->faker->realText,
            'price' => 2132, // $21.32
            'currency' => 'USD',
            'attachments' => $attachments,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);
        $cmgCountAfter = Chatmessagegroup::count();
        $msgCountAfterStore = Chatmessage::count();

        $response->assertJsonStructure([
            'chatmessagegroup' => [ 'id', 'mgtype', 'sender_id' ],
            'chatthreads' => [
                0 => [ 'id', 'originator_id', 'is_tip_required', 'created_at' ],
            ],
            'chatmessages' => [ 
                0 => [ 'id', 'chatthread_id', 'mcontent', 'sender_id', 'deliver_at', 'is_delivered', 'is_read', 'is_flagged' ],
            ],
        ]);

        $cmg = Chatmessagegroup::find($content->chatmessagegroup->id);
        $this->assertNotNull($cmg);
        $this->assertNotNull($cmg->id);

        $this->assertEquals($fans->count(), count($content->chatthreads));

        $ct = Chatthread::find($content->chatthreads[0]->id);
        $this->assertNotNull($ct);
        $this->assertNotNull($ct->id);

        $this->assertEquals($cmgCountBefore+1, $cmgCountAfter);

        // --- Do a purchase by one of the fans/msg-receivers ---

        $cm = Chatmessage::find($content->chatmessages[0]->id);
        $this->assertNotNull($cm->id??null);
        $this->assertNotNull($cm->price);
        $this->assertGreaterThan(0, $cm->price->getAmount());
        $receiver = $cm->chatthread->participants()->where('users.id', '<>', $originator->id)->first();
        $this->assertNotEquals($originator->id, $receiver->id);

        $events = Event::fake([ ItemPurchased::class, PurchaseFailed::class ]);
        $account = $receiver->financialAccounts()->where('type', AccountTypeEnum::IN)->with('resource')->first();
        $payload = [
            'account_id' => $account->getKey(),
            'amount'     => $cm->price->getAmount(),
            'currency'   => $cm->currency, // 'USD'
            'campaign'   => null,
            'message'    => null, // $this->faker->realText,
        ];

        $response = $this->actingAs($receiver)->ajaxJSON('PUT', route('chatmessages.purchase', ['chatmessage'=>$cm->id]), $payload);
        $response->assertStatus(200);

        // ItemPurchased will update client with websocket event
        Event::assertDispatched(ItemPurchased::class);
        Event::assertNotDispatched(PurchaseFailed::class);

        // Amount from Card
        $this->assertDatabaseHas('transactions', [
            'account_id' => $account->getKey(),
            'debit_amount' => $cm->price->getAmount(),
        ], 'financial');

        // Amount from fan/msg-receiver to creator/originator
        $this->assertDatabaseHas('transactions', [
            'account_id' => $receiver->getWalletAccount('segpay', 'USD')->getKey(),
            'debit_amount' => $cm->price->getAmount(),
            'resource_id' => $cm->getKey(),
        ], 'financial');

        // Amount to creator/originator from fan/msg-receiver
        $this->assertDatabaseHas('transactions', [
            'account_id' => $originator->getEarningsAccount('segpay', 'USD')->getKey(),
            'credit_amount' => $cm->price->getAmount(),
            'resource_id' => $cm->getKey(),
        ], 'financial');

        $response = $this->actingAs($originator)->ajaxJSON('GET', route('chatmessagegroups.index'));
        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data' => [
                0 => [ 
                    'id', 
                    'sender_id', 
                    'mgtype', 
                    'price', 
                    'currency', 
                    'mcontent', 
                    'sender_name', 
                    'deliver_at', 
                    'attachment_counts'=>[
                        'images_count',
                        'videos_count',
                        'audios_count',
                    ],
                    'sent_count',
                    'read_count',
                    'purchased_count',
                    'cattrs',
                    'created_at',
                ],
            ],
        ]);
        //dd($content);

        // Find the one we just created in the response
        $mmaR = collect($content->data)->first( function($v) use(&$cmg) { // mass media analytics response
            return $v->id === $cmg->id;
        });
        $this->assertNotNull($mmaR->id??null);
        $this->assertEquals(1, $mmaR->purchased_count);

    } // test_can_analyze_mass_message_stats()

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

