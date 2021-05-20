<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;
use DB;
use Exception;
use Carbon\Carbon;

use App\Models\User;
use App\Models\ChatThread;
use App\Models\Message;
use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;

class MessagesTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('MessagesTableSeeder'); // $this->{output, faker, appEnv}

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Messages seeder: loaded ".$posts->count()." posts...");
        }

        $senderCount = $this->faker->numberBetween(5, 12); // number of senders

        $senders = User::take($senderCount)->get();

        $senders->each( function($s) {

            $threadCount = $this->faker->numberBetween(1, 3); // number of receivers *per* sender (ie threads)
            $receivers = User::take($threadCount)->where('id', '<>', $s->id)->get();

            $receivers->each( function($r) use(&$s) {
                $chatthread = $s->chatthreads()->create([
                    'receiver_id' => $r->id,
                    //'tip_price' => $request->input('tip_price'),
                    //'schedule_datetime' => $schedule_datetime,
                ]);
                $msgCount = $this->faker->numberBetween(1, 5);
                for ( $i = 0 ; $i < $msgCount ; $i++ ) {
                    $chatthread->messages()->create([
                        'mcontent' => $this->faker->realText,
                    ]);
                }
            });


        });

    }

}
