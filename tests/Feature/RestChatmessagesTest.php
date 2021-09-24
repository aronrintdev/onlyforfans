<?php
namespace Tests\Feature;

use DB;
use Tests\TestCase;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

use Database\Seeders\TestDatabaseSeeder;

use App\Models\Chatmessage;
use App\Models\Chatthread;
use App\Models\Mediafile;
use App\Models\Mycontact;
use App\Models\Timeline;
use App\Models\User;
use App\Models\Vault;
use App\Models\Vaultfolder;

use App\Enums\MediafileTypeEnum;
use App\Enums\Financial\AccountTypeEnum;

/**
 * @group chatmessages
 * @package Tests\Feature
 */
class RestChatmessagesTest extends TestCase
{
    use WithFaker;

    /**
     *  @group chatmessages
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_chatmessages()
    {
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());

        $payload = [ 
            'take' => 100,
        ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('chatmessages.index', $payload) );

        $response->assertStatus(200);
        $content = json_decode($response->content());
        $response->assertJsonStructure([
            'data' => [
                0 => [
                    'chatthread_id', 
                    'sender_id', 
                    'mcontent', 
                    'deliver_at', 
                    'is_delivered', 
                    'is_read', 
                    'is_flagged', 
                    'delivered_at', 
                ],
            ],
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);
        //dd($content->messages);
        // test comment

        // Check no messages from threads in which I am not a participant
        $chatmessages = collect($content->data);
        $num = $chatmessages->reduce( function($acc, $cm) use(&$sessionUser) {
            $chatmessage = Chatmessage::find($cm->id);
            $this->assertNotNull($chatmessage);
            return ($chatmessage->chatthread->participants->contains($sessionUser->id)) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread in which session user is not a participant (of '.$content->meta->total.' total messages)');

        // Make sure no scheduled but undelivered messages are returned
        $num = $chatmessages->reduce( function($acc, $cm) {
            return $cm->is_delivered ? $acc : ($acc+1); // expect is_delivered to be TRUE
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread which is not marked "delivered"');
    }


    /**
     *  @group chatmessages
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_chatmessages_filtered_by_thread()
    {
        $sessionUser = User::has('chatthreads')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());
        $chatthread = $sessionUser->chatthreads[0];

        $payload = [ 'take' => 100, 'chatthread_id' => $chatthread->id ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('chatmessages.index', $payload) );

        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);

        // Check all messages belong to indicated thread
        $chatmessages = collect($content->data);
        $num = $chatmessages->reduce( function($acc, $cm) use(&$chatthread) {
            $_cm = Chatmessage::findOrFail($cm->id);
            return ($_cm->chatthread->id===$chatthread->id) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread other than the one filtered on');

        // Make sure no scheduled but undelivered messages are returned
        $num = $chatmessages->reduce( function($acc, $cm) {
            return $cm->is_delivered ? $acc : ($acc+1); // expect is_delivered to be TRUE
        }, 0);
        $this->assertEquals(0, $num, 'Found chatmessage in thread which is not marked "delivered"');
    }

    /**
     *  @group chatmessages
     *  @group regression
     *  @group regression-base
     *  @group here0923
     */
    public function test_can_send_chatmessage_text_only_in_thread()
    {
        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
        $originator = $timeline->user; // aka creator
        $fans = $timeline->followers; // aka receivers
        $this->assertGreaterThan(0, $fans->count());

        // --- Send the message ---

        $receiver = $fans[0];

        $payload = [
            'originator_id' => $originator->id,
            'participants' => [$receiver->id],
            'mcontent' => $this->faker->realText,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'chatthreads' => [
                0 => [ 
                    'id', 
                    'originator_id', 
                    'is_tip_required', 
                    'created_at',
                    'participants' => [
                        0 => [
                            'id',
                            'username',
                            'name',
                            'avatar' => ['filepath'],
                            'cover' => ['filepath'],
                            'about',
                        ],
                    ],
                ],
            ],
            'chatmessages' => [
                0 => [ 
                    'id', 
                    'chatthread_id', 
                    'sender_id',
                    'mcontent',
                    'deliver_at',
                    'is_delivered',
                    'delivered_at',
                    'is_read',
                    'is_flagged',
                    'cattrs',
                    'created_at',
                    'currency',
                    'price',
                    'purchase_only',
                    'chatmessagegroup_id',
                    'sender' => [
                        'id',
                        'username',
                        'name',
                    ],
                    'chatthread', // %FIXME: redundant?
                ],
            ],
        ]);

        $ct = Chatthread::find($content->chatthreads[0]->id);
        $this->assertNotNull($ct->id??null);

        $this->assertIsArray($content->chatthreads);
        $this->assertEquals(1,  count($content->chatthreads) );
        $ctR = $content->chatthreads[0];
        $this->assertEquals($originator->id, $ctR->originator_id);
        $this->assertEquals(2,  count($ctR->participants) );
        $this->assertTrue( collect($ctR->participants)->contains( function($v) use(&$receiver) {
            return $v->id === $receiver->id;
        })); // check receiver-as-participant 

        $this->assertIsArray($content->chatmessages);
        $this->assertEquals(1,  count($content->chatmessages) );
        $cmR = $content->chatmessages[0];
        $this->assertEquals($originator->id, $cmR->sender_id);
        $this->assertEquals($payload['mcontent'], $cmR->mcontent);
        $this->assertTrue($cmR->is_delivered);
        $this->assertFalse(!!$cmR->is_read);
        $this->assertFalse($cmR->purchase_only);
        $this->assertEquals(0, $cmR->price);

    }

    /**
     *  @group chatmessages
     *  @group regression
     *  @group regression-base
     *  @group here0923
     */
    public function test_can_send_chatmessage_with_one_attachment()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
        $originator = $timeline->user; // aka creator
        $fans = $timeline->followers; // aka receivers of mass message
        $this->assertGreaterThan(0, $fans->count());

        // -------------------------------------------------------------------------------------------
        // Need to simulate what Create Chatmessage form does, which is to upload mediafiles first
        // to vault, *then* store the [chatmessages] record with the attachments passed in POST payload
        // -------------------------------------------------------------------------------------------

        $filenames = [
            $this->faker->slug,
        ];
        $files = [
            UploadedFile::fake()->image($filenames[0], 200, 200),
        ];

        $primaryVault = Vault::primary($originator)->first();
        $vf = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        $attachments = [];

        // upload file
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

        $this->assertGreaterThan(0, count($attachments));

        // --- Send the Chat Message ---

        $receiver = $fans[0];

        $payload = [
            'originator_id' => $originator->id,
            'participants' => [$receiver->id],
            'mcontent' => $this->faker->realText,
            'attachments' => $attachments,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);
        $response->assertJsonStructure([ 'chatthreads'=>[0=>['id']], 'chatmessages'=>[0=>['id']] ]); // see above for detailed check
        $ct = Chatthread::find($content->chatthreads[0]->id);
        $this->assertNotNull($ct->id??null);

        $mediafiles = Mediafile::where('resource_type', 'chatmessages')->where('resource_id', $content->chatmessages[0]->id)->get();
        $this->assertEquals(1, $mediafiles->count());
        $this->assertEquals($filenames[0], $mediafiles[0]->mfname);
    }

    /**
     *  @group chatmessages
     *  @group regression
     *  @group regression-base
     *  @group here0923
     */
    public function test_can_send_chatmessage_with_multiple_attachments()
    {
        Storage::fake('s3');

        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
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

        // upload file 1
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

        // upload file 2
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

        $this->assertEquals(2, count($attachments));

        // --- Send the Chat Message ---

        $receiver = $fans[0];

        $payload = [
            'originator_id' => $originator->id,
            'participants' => [$receiver->id],
            'mcontent' => $this->faker->realText,
            'attachments' => $attachments,
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);
        $response->assertJsonStructure([ 'chatthreads'=>[0=>['id']], 'chatmessages'=>[0=>['id']] ]); // see above for detailed check
        $ct = Chatthread::find($content->chatthreads[0]->id);
        $this->assertNotNull($ct->id??null);

        $mediafiles = Mediafile::where('resource_type', 'chatmessages')->where('resource_id', $content->chatmessages[0]->id)->get();
        $this->assertEquals(2, $mediafiles->count());
    }

    /**
     *  @group chatmessages
     *  @group regression
     *  @group regression-base
     *  @group here0923
     */
    public function test_can_create_purchase_only_chatmessage()
    {
        Storage::fake('s3');
        $this->assertTrue(Config::get('segpay.fake'), 'Your SEGPAY_FAKE .env variable is not set to true.');

        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
        $originator = $timeline->user; // aka creator
        $fans = $timeline->followers; // aka receivers of mass message

        $this->assertGreaterThan(0, $fans->count());

        // -------------------------------------------------------------------------------------------
        // Need to simulate what Create Chatmessage form does, which is to upload mediafiles first
        // to vault, *then* store the [chatmessages] record with the attachments passed in POST payload
        // -------------------------------------------------------------------------------------------

        $filenames = [
            $this->faker->slug,
        ];
        $files = [
            UploadedFile::fake()->image($filenames[0], 200, 200),
        ];

        $primaryVault = Vault::primary($originator)->first();
        $vf = Vaultfolder::isRoot()->where('vault_id', $primaryVault->id)->first();

        $attachments = [];

        // upload file
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

        $this->assertGreaterThan(0, count($attachments));

        // --- Send the Chat Message ---

        $receiver = $fans[0];
        $this->assertFalse($receiver->isAdmin());

        $payload = [
            'originator_id' => $originator->id,
            'participants' => [$receiver->id],
            'mcontent' => $this->faker->realText,
            'attachments' => $attachments,
            'price' => 2132, // $21.32
            'currency' => 'USD',
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(201);
        $response->assertJsonStructure([ 'chatthreads'=>[0=>['id']], 'chatmessages'=>[0=>['id']] ]); // see above for detailed check
        $ct = Chatthread::find($content->chatthreads[0]->id); // the chatthread just created that contains the priced message
        $this->assertNotNull($ct->id??null);

        $ctR = $content->chatthreads[0];
        $this->assertEquals($originator->id, $ctR->originator_id);
        $this->assertEquals(2,  count($ctR->participants) );
        $this->assertTrue( collect($ctR->participants)->contains( function($v) use(&$receiver) {
            return $v->id === $receiver->id;
        })); // check receiver-as-participant 
        unset($ctR);

        $mediafiles = Mediafile::where('resource_type', 'chatmessages')->where('resource_id', $content->chatmessages[0]->id)->get();
        $this->assertEquals(1, $mediafiles->count());

        $this->assertEquals(1,  count($content->chatmessages) );
        $chatmessagesR = $content->chatmessages;
        $cmR1 = $content->chatmessages[0];
        $this->assertEquals($originator->id, $cmR1->sender_id);
        $this->assertTrue($cmR1->purchase_only);
        $this->assertEquals($payload['price'], $cmR1->price);

        // --- Check denied access before purchase --- %FIXME %ERIK

        $response = $this->actingAs($receiver)->ajaxJSON( 'GET', route('chatmessages.index', ['chatthread_id'=>$ct->id,'take'=>100]) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        //dd($content);
        $response->assertJsonStructure([
            'data' => [
                0 => [ 'chatthread_id', 'sender_id', 'mcontent', 'deliver_at', 'is_delivered', 'is_read', 'is_flagged', 'delivered_at' ]
            ],
        ]);
        // Find the message in the response to chatmessages.index
        $cmR2 = collect($content->data)->first( function($v) use(&$cmR1) {
            return $cmR1->id === $v->id;
        });
        $this->assertNotNull($cmR2->id??null);
        $this->assertFalse($cmR2->has_access);
        $this->assertIsArray($cmR2->purchased_by);
        $this->assertEquals(0, count($cmR2->purchased_by));
        $this->assertIsArray($cmR2->attachments);
        $this->assertEquals(0, count($cmR2->attachments));

        // --- Do a purchase by fan/receiver ---

        $cm = Chatmessage::find($chatmessagesR[0]->id);
        $this->assertNotNull($cm->id??null);
        $this->assertNotNull($cm->price);
        $this->assertGreaterThan(0, $cm->price->getAmount());

        $events = Event::fake([ ItemPurchased::class, PurchaseFailed::class ]);
        $receiver->refresh();
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
        Event::assertDispatched(ItemPurchased::class); // %FIXME %ERIK
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

        // --- Check allowed access afer purchase ---

        $response = $this->actingAs($receiver)->ajaxJSON( 'GET', route('chatmessages.index', ['chatthread_id'=>$ct->id,'take'=>100]) );
        $response->assertStatus(200);
        $content = json_decode($response->content());
        // Find the message in the response to chatmessages.index
        $cmR3 = collect($content->data)->first( function($v) use(&$cmR1) {
            return $cmR1->id === $v->id;
        });
        $this->assertNotNull($cmR3->id??null);
        $this->assertTrue($cmR3->has_access);
        $this->assertIsArray($cmR2->purchased_by);
        $this->assertEquals(1, count($cmR2->purchased_by));
        //$this->assertEquals($receiver_id, $cmR3->purchased_by);
        $this->assertIsArray($cmR2->attachments);
        $this->assertEquals(1, count($cmR2->attachments));
    }

    /**
     *  @group chatmessages
     *  @group regression
     *  @group regression-base
     *  @group here0923
     */
    public function test_create_purchase_only_chatmessage_must_have_attachments()
    {
        $timeline = Timeline::has('followers', '>=', 2)->firstOrFail();
        $originator = $timeline->user; // aka creator
        $fans = $timeline->followers; // aka receivers of mass message

        $this->assertGreaterThan(0, $fans->count());

        // --- Send the Chat Message ---

        $receiver = $fans[0];

        $payload = [ // no attachments!
            'originator_id' => $originator->id,
            'participants' => [$receiver->id],
            'mcontent' => $this->faker->realText,
            'price' => 2132, // $21.32
            'currency' => 'USD',
        ];
        $response = $this->actingAs($originator)->ajaxJSON( 'POST', route('chatthreads.store', $payload) );
        $content = json_decode($response->content());
        $response->assertStatus(422);
    }


    /**
     *  @group chatmessages
     *  @group mycontacts
     *  @group regression
     *  @group regression-base
     */
    public function test_can_list_mycontacts()
    {
        $sessionUser = User::has('mycontacts')->firstOrFail();
        $this->assertFalse($sessionUser->isAdmin());

        $payload = [ 'take' => 100 ];
        $response = $this->actingAs($sessionUser)->ajaxJSON( 'GET', route('mycontacts.index', $payload) );

        $response->assertStatus(200);
        $content = json_decode($response->content());

        $mycontacts = collect($content->data);
        $this->assertGreaterThan(0, $mycontacts->count());

        // Check all mycontacts belong to session user
        $num = $mycontacts->reduce( function($acc, $mc) use(&$sessionUser) {
            return ($mc->owner_id===$sessionUser->id) ? $acc : ($acc+1);
        }, 0);
        $this->assertEquals(0, $num, 'Found contact in results that does not belong to session user');
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

