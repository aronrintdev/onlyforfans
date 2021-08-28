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
        $originatorCount = 3;

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

            $attrs = [
                'originator_id'  => $o->id, // 'required|uuid|exists:users,id',
                'participants'   => $receivers->pluck('id')->toArray(), // 'required|array', // %FIXME: rename to 'recipients' for clairty
                'mcontent'       => $this->faker->realText, // 'string',  // optional first message content
                'deliver_at'     => $isScheduled ? $deliverAt : null, // 'numeric', // optional to pre-schedule delivery of message if present
                //'price'          => // 'numeric',
                //'currency'       => 'USD', // 'required_with:price|size:3',
                //'attachments'    => // 'required_with:price|array',   // optional first message attachments
            ];

            ['chatthreads'=>$chatthreads, 'chatmessagegroup'=>$cmGroup, 'chatmessages'=>$chatmessages] = Chatthread::findOrCreateChat($o, $attrs);

            // Set $ts & update message timestamps and possibly .is_delivered field...
            if ( $isScheduled && $isDeliveredByNow ) {
                $ts = $now->subDays( $this->faker->numberBetween(1,7) );
                $chatmessages->each( function($cm) { // mark each as 'delivered'
                    $cm->isDelivered = true;
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
                $ts = new Carbon( $this->faker->dateTimeBetween($startDate = '-2 years', $endDate = '-1 months') );
                $chatmessages->each( function($cm) use($ts) {
                    $cm->created_at = $ts;
                    $cm->updated_at = $ts;
                    $cm->save();
                });
            }


            // Replies to simulate a conversation
            //$baseTS = $ts->copy();
            $chatthreads->each( function($r) use(&$ct) {
                $replyCount = $this->faker->numberBetween(1, 18);
                $_ts = $ts->copy()->addMinutes( $this->faker->numberBetween(1,70) );
                for ( $i = 0 ; $i < $replyCount ; $i++ ) {
                    $sender = $this->faker->randomElement( $ct->participants->toArray() ); // so it looks like a back-and-forth conversation
                    $attrs = [
                        'mcontent'    => $this->faker->realText, // 'string',  // optional first message content
                        //'price'       => 'numeric',
                        //'currency'    => 'required_with:price|size:3',
                        //'attachments' => 'required_with:price|array',
                    ];
                    $cm = $ct->sendMessage($sender, $attrs);
                    $cm->created_at = $_ts;
                    $cm->updated_at = $_ts;
                    $cm->save();
                    $_ts->addMinutes( $this->faker->numberBetween(1,70) );
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

    private function setAttrs() 
    {
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
            $attrs = [
                'chatthread_id' => $chatthread->id,
                'sender_id' => $senderID,
                'mcontent' => $this->faker->realText,
                'is_delivered' => $isDelivered,
                'deliver_at' => $deliverAt,
            ];
        } else {
            // 'Instantanious' Messages
            $ts = new Carbon( $this->faker->dateTimeBetween($startDate = '-2 years', $endDate = '-1 months') );
            $attrs = [
                'chatthread_id' => $chatthread->id,
                'sender_id' => $senderID,
                'mcontent' => $this->faker->realText,
                'is_read' => $this->faker->boolean(60),
            ];
        }
        //$ts->addMinutes( $this->faker->numberBetween(1,70) );
        //$senderID = $this->faker->randomElement([ $o->id, $r->id ]); // so it looks like a back-and-forth conversation
    }

}
