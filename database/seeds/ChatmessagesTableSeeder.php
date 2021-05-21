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

        //$senderCount = $this->faker->numberBetween(2, 4); // number of senders
        $originatorCount = 3;

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Chatmessages seeder: loaded ".$originatorCount." originators...");
        }

        $originators = User::has('timeline')->take($originatorCount)->get();

        $originators->each( function($o) {

            $threadCount = $this->faker->numberBetween(1, 3); // number of receivers *per* originator (ie threads)
            $receivers = User::has('timeline')->take($threadCount)->where('id', '<>', $o->id)->get();

            // %TODO: add group chats
            $receivers->each( function($r) use(&$o) {
                //dump('ts', $ts->toDateTimeString());

                $chatthread = Chatthread::create([
                    'originator_id' => $o->id,
                ]);

                $chatthread->participants()->attach($r->id);

                $ts = new Carbon( $this->faker->dateTimeBetween($startDate = '-2 years', $endDate = '-1 months') );
                $msgCount = $this->faker->numberBetween(3, 15);

                $senderID = $o->id; // init sender
                for ( $i = 0 ; $i < $msgCount ; $i++ ) {
                    $m = Chatmessage::create([
                        'chatthread_id' => $chatthread->id,
                        'sender_id' => $senderID,
                        'mcontent' => $this->faker->realText,
                    ]);
                    $m->created_at = $ts;
                    $m->updated_at = $ts;
                    $m->save();
                    $ts->addMinutes( $this->faker->numberBetween(1,70) );
                    $senderID = $this->faker->randomElement([ $o->id, $r->id ]); // so it looksl like a back-and-forth conversation
                }
            });

        });

    }

}
