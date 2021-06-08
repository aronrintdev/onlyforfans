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

        //$senderCount = $this->faker->numberBetween(2, 4); // number of senders
        $originatorCount = 3;

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Chatmessages seeder: loaded ".$originatorCount." originators...");
        }

        $originators = User::has('timeline')->take($originatorCount)->get();

        $originators->each( function($o) {

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
            });
        });

        // 'touch' the 'updated_at' column for all theads based on latest messeage
        $chatthreads = Chatthread::get();
        $chatthreads->each( function($ct) {
            //$latestMsg = $ct->chatmessages->where('is_delivered', true)->orderBy('created_at', 'desc')->first();
            $latestMsg = Chatmessage::where('chatthread_id', $ct->id)
                ->where('is_delivered', true)
                ->latest()->first();
            DB::table('chatthreads')->where('id', $ct->id)->update([
                'updated_at' => $latestMsg->created_at,
            ]);
        });

    } // run()

}
