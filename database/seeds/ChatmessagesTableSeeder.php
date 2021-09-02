<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;
use DB;
use Exception;
use Carbon\Carbon;

use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;
use App\Models\User;
use App\Models\Chatthread;
use App\Models\Chatmessage;

class ChatmessagesTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('ChatmessagesTableSeeder'); // $this->{output, faker, appEnv}

        if ( 0 ) {
            DB::table('chatthread_user')->truncate();
            DB::table('chatmessages')->truncate();
            //DB::table('chatthreads')->truncate();
            DB::table('chatthreads')->delete();
            DB::table('mycontacts')->delete();
            dd('truncate done');
        }

        // ----

        //$senderCount = $this->faker->numberBetween(2, 4); // number of senders
        $originatorCount = 7;

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Chatmessages seeder: loaded ".$originatorCount." originators...");
        }

        $originators = User::has('timeline')->take($originatorCount)->get();

        $originators->each( function($o) {

            $isScheduled = false; // init
            $isDeliveredByNow = false; // init
            $now = Carbon::now();

            $isMassMessage = $this->faker->boolean(50);
            $take = $isMassMessage ? $this->faker->numberBetween(2, 5) : 1; // number of receivers *per* originator (ie threads)
            $receivers = User::has('timeline')->where('id', '<>', $o->id)->take($take)->get();

            $isScheduled = $this->faker->boolean(20);
            if ( $isScheduled ) {
                $isDeliveredByNow = $this->faker->boolean(50);
                $deliverAt = $isDeliveredByNow 
                    ? $now->subDays( $this->faker->numberBetween(1,5) ) // past: already delivered
                    : $now->addDays( $this->faker->numberBetween(1,7) ); // future: to be delivered
            }

            $isPriced = $this->faker->boolean(50);
            $currency = $isPriced ? 'USD' : null;
            $price = $isPriced ? ($this->faker->numberBetween(5, 199) * 100) : null;

            $rattrs = (object)[
                'originator_id'  => $o->id, // 'required|uuid|exists:users,id',
                'participants'   => $receivers->pluck('id')->toArray(), // 'required|array', // %FIXME: rename to 'recipients' for clairty
                'mcontent'       => $this->faker->realText, // 'string',  // optional first message content
                'deliver_at'     => $isScheduled ? $deliverAt->timestamp : null, // 'numeric', // optional to pre-schedule delivery of message if present
                'price'          => $price, // 'numeric',
                'currency'       => $currency, // 'required_with:price|size:3',
                //'attachments'    => // 'required_with:price|array',   // optional first message attachments
            ];

            $this->output->writeln("    ~ create 'thread' for ".$o->name." : ".json_encode($rattrs)."...");

            ['chatthreads'=>$chatthreads, 'chatmessages'=>$chatmessages, 'chatmessagegroup'=>$cmGroup] = Chatthread::findOrCreateChat(
                $o,                            // User     $sender
                $rattrs->originator_id,        // string   $originatorId
                $rattrs->participants,         // array    $participants (array of user ids)
                $rattrs->mcontent ?? '',       // string   $mcontent
                $rattrs->deliver_at ?? null,   // int      $deliver_at
                $rattrs->attachments ?? null,  // array    $attachments
                $rattrs->price ?? null,        // int      $price
                $rattrs->currency ?? null,     // string   $currency
                ['foo'=>'bar'],                // array    $cattrs
            );

            // Set $ts & update message timestamps and possibly .is_delivered field...
            if ( $isScheduled && $isDeliveredByNow ) {
                $ts = $now->subDays( $this->faker->numberBetween(1,7) );
                $chatmessages->each( function($cm) use ($ts) { // mark each as 'delivered'
                    $cm->is_delivered = true;
                    $cm->created_at = $ts;
                    $cm->updated_at = $ts;
                    $cm->save();
                });
            } else if ( $isScheduled ) {
                //$ts = $deliverAt;
                //$ts = new Carbon( $this->faker->dateTimeBetween($startDate = '-2 weeks', $endDate = '-1 days') );
                $ts = $now->subDays( $this->faker->numberBetween(1,14) );
                $chatmessages->each( function($cm) use($ts) {
                    $cm->created_at = $ts;
                    $cm->updated_at = $ts;
                    $cm->save();
                });
            } else {
                $ts = new Carbon( $this->faker->dateTimeBetween($startDate = '-3 years', $endDate = '-2 months') );
                $chatmessages->each( function($cm) use($ts) {
                    $cm->created_at = $ts;
                    $cm->updated_at = $ts;
                    $cm->save();
                });
            }

            // Replies to simulate a conversation
            //$baseTS = $ts->copy();
            $chatthreads->each( function($ct) use($ts, $now) {
                $replyCount = $this->faker->numberBetween(1, 18);
                $_ts = $ts->copy()->addSeconds( $this->faker->numberBetween(1,99) );
                for ( $i = 0 ; $i < $replyCount ; $i++ ) {
                    $this->output->writeln("    ~ reply on ".$ct->id.", #$i of $replyCount...");
                    $senderA = $this->faker->randomElement( $ct->participants->toArray() ); // so it looks like a back-and-forth conversation
                    $sender = User::findOrFail($senderA['id']);
                    $rattrs = (object)[
                        'mcontent'    => $this->faker->realText, // 'string',  // optional first message content
                        //'price'       => 'numeric',
                        //'currency'    => 'required_with:price|size:3',
                        //'attachments' => 'required_with:price|array',
                    ];
                    $cm = $ct->sendMessage(
                        $sender, 
                        $rattrs->mcontent ?? null, // string $mcontent = ''
                        $rattrs->attachments ?? [], // array $attachments = []
                        $rattrs->price ?? null, // $price = null
                        $rattrs->currency ?? null // $currency = null
                    );
                    if ( $_ts->greaterThan($now) ) {
                        $_ts = $now; // (max): safety code to prevent conversation replies extending into future
                    }
                    $cm->created_at = $_ts;
                    $cm->updated_at = $_ts;
                    $cm->save();
                    $_ts->addSeconds( $this->faker->numberBetween(1,99) );
                }
            });

                /*
            $threadCount = $this->faker->numberBetween(1, 3); // number of receivers *per* originator (ie threads)
            $receivers = User::has('timeline')->where('id', '<>', $o->id)->take($threadCount)->get();

            $receivers->each( function($r) use(&$o) {
                //dump('ts', $ts->toDateTimeString());

                $chatthread = Chatthread::create([
                    'originator_id' => $o->id,
                ]);

                $chatthread->participants()->attach($r->id);

                $msgCount = $this->faker->numberBetween(1, 18);

                $senderID = $o->id; // init sender
                for ( $i = 0 ; $i < $msgCount ; $i++ ) {
                    $isScheduled = $this->faker->boolean(20);
                    if ($isScheduled) {
                        // Pre-schedules messages (some may be marked delivered)
                        $now = Carbon::now();
                        $isDeliveredByNow = $this->faker->boolean(50);
                        if ( $isDeliveredByNow ) {
                            $deliverAt = $now->subDays( $this->faker->numberBetween(1,5) );
                            $ts = $deliverAt;
                            $isDelivered = true;
                        } else {
                            $deliverAt = $now->addDays( $this->faker->numberBetween(1,7) );
                            $ts = $now->subDays( $this->faker->numberBetween(1,7) );
                            $isDelivered = false;
                        }
                        //Carbon( $this->faker->dateTimeBetween($startDate = '-2 years', $endDate = '-1 months') );
                        $m = Chatmessage::create([
                            'chatthread_id' => $chatthread->id,
                            'sender_id' => $senderID,
                            'mcontent' => $this->faker->realText,
                            'is_delivered' => $isDelivered,
                            'deliver_at' => $deliverAt,
                        ]);
                        $m->created_at = $ts;
                        $m->updated_at = $ts;
                        $m->save();
                    } else {
                        // 'Instantanious' Messages
                        $ts = new Carbon( $this->faker->dateTimeBetween($startDate = '-2 years', $endDate = '-1 months') );
                        $m = Chatmessage::create([
                            'chatthread_id' => $chatthread->id,
                            'sender_id' => $senderID,
                            'mcontent' => $this->faker->realText,
                            'is_read' => $this->faker->boolean(60),
                        ]);
                        $m->created_at = $ts;
                        $m->updated_at = $ts;
                        $m->save();
                    }
                    $ts->addMinutes( $this->faker->numberBetween(1,70) );
                    $senderID = $this->faker->randomElement([ $o->id, $r->id ]); // so it looks like a back-and-forth conversation
                }

            }); // each($r)
                 */

        }); // each($o)

        // ----

        // 'touch' the 'updated_at' column for all theads based on latest message
        $chatthreads = Chatthread::get();
        $chatthreads->each( function($ct) {
            //$latestMsg = $ct->chatmessages->where('is_delivered', true)->orderBy('created_at', 'desc')->first();
            $latestMsg = Chatmessage::where('chatthread_id', $ct->id)
                ->where('is_delivered', true)
                ->latest()->first();
            if ( $latestMsg ) {
                DB::table('chatthreads')->where('id', $ct->id)->update([
                    'updated_at' => $latestMsg->created_at,
                ]);
            }
        });

    } // run()

}
