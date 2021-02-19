<?php
namespace Database\Seeders;

use DB;
use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use App\Libs\UserMgr;
use App\Models\Timeline;
use App\Models\Fanledger;
use App\Enums\PostTypeEnum;
use App\Libs\UuidGenerator;
use App\Libs\FactoryHelpers;
use App\Enums\PaymentTypeEnum;
use App\Enums\MediafileTypeEnum;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Output\ConsoleOutput;

class ShareablesTableSeeder extends Seeder
{
    use SeederTraits;

    public function run()
    {
        $this->initSeederTraits('ShareablesTableSeeder'); // $this->{output, faker, appEnv}

        // +++ Create ... +++

        $timelines = Timeline::get();

        if ( $this->appEnv !== 'testing' ) {
            $this->output->writeln("  - Shareables seeder: loaded ".$timelines->count()." timelines...");
        }

        // Remove a few timelines so we have some without any followers for testing...
        //   ~ [ ] %TODO: timelines w/ followers but not subcribers, & vice-versa
        $timelines->pop();
        $timelines->pop();
        $timelines->pop();

        $timelines->each( function($t) {

            static $iter = 1;

            // --- user pool ---

            $userPool = User::where('id', '<>', $t->user->id)->get(); // exclude timeline owner
            $followerPool = $userPool;
            unset($userPool);

            // --- create some followers (non-premium, will upgrade some later) ---

            $max = $this->faker->numberBetween( 2, min($followerPool->count()-1, $this->getMax('follower')+$this->getMax('subscriber')) );
            if ( $max < 2 ) {
                throw new Exception('Requires at least 2 followers per timeline - max:'.$max);
            }
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Creating $max (non-premium) followers for timeline ".$t->name.", iter: $iter");
            }

            $followerPool->random($max)->each( function($f) use(&$t) {
                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.follow_some_free_timelines' ];
                DB::table('shareables')->insert([
                    'sharee_id' => $f->id,
                    'shareable_type' => 'timelines',
                    'shareable_id' => $t->id,
                    'is_approved' => 1,
                    'access_level' => 'default',
                    'cattrs' => json_encode($customAttributes),
                ]);

                // --- purchase some posts ---

                $max = $this->faker->numberBetween( 0, $this->getMax('purchased') );
                $purchaseablePosts = $t->posts()->where('type', PostTypeEnum::PRICED)->inRandomOrder($max)->get();
                $count = $purchaseablePosts->count();
                if ( $this->appEnv !== 'testing' ) {
                    $this->output->writeln("  - Purchasing $count posts for follower ".$f->name." on timeline ".$t->name);
                }
                if ( $count > 0 ) {
                    $purchaseablePosts->each( function($p) use(&$f) {
                        $customAttributes = [ 'notes' => 'ShareablesTableSeeder.purchase_post_as_follower_free_timeline' ];
                        // %FIXME: this should also update shareables, and be encapsualted in a method in model or class-lib
                        DB::table('shareables')->insert([
                            'sharee_id' => $f->id,
                            'shareable_type' => 'posts',
                            'shareable_id' => $p->id,
                            'is_approved' => 1,
                            'access_level' => 'premium', // ??
                            'cattrs' => json_encode($customAttributes),
                        ]);
                        Fanledger::create([
                            //'from_account' => , // %TODO: see https://trello.com/c/LzTUmPCp
                            //'to_account' => ,
                            'fltype' => PaymentTypeEnum::PURCHASE, 
                            'purchaser_id' => $f->id, // fan
                            'seller_id' => $p->user->id,
                            'purchaseable_type' => 'posts',
                            'purchaseable_id' => $p->id,
                            'qty' => 1,
                            'base_unit_cost_in_cents' => $p->price,
                            'cattrs' => json_encode($customAttributes),
                        ]);
                    });
                }
            });

            // --- Select some for upgrades ---

            $t->refresh();
            $followers = $t->followers;
            $max = $this->faker->numberBetween( 0, min($followers->count()-1, $this->getMax('subscriber')) );
            if ( $this->appEnv !== 'testing' ) {
                $this->output->writeln("  - Upgrading $max followers to subscribers for timeline ".$t->name.", iter: $iter");
            }

            $followers->random($max)->each( function($f) use(&$t) {
                $customAttributes = [ 'notes' => 'ShareablesTableSeeder.upgraded_to_subscriber' ];
                DB::table('shareables')
                    ->where('sharee_id', $f->id)
                    ->where('shareable_type', 'timelines')
                    ->where('shareable_id', $t->id)
                    ->update([
                        'access_level' => 'premium', // ??
                        'cattrs' => json_encode($customAttributes),
                    ]);
                Fanledger::create([
                    //'from_account' => , // %TODO: see https://trello.com/c/LzTUmPCp
                    //'to_account' => ,
                    'fltype' => PaymentTypeEnum::PURCHASE, 
                    'purchaser_id' => $f->id, // fan
                    'seller_id' => $t->user->id,
                    'purchaseable_type' => 'timelines',
                    'purchaseable_id' => $t->id,
                    'qty' => 1,
                    'base_unit_cost_in_cents' => $t->price,
                    'cattrs' => json_encode($customAttributes),
                ]);
            });

            $iter++;
        });

    } // run()

    private function getMax($param) : int
    {
        static $max = [
            'testing' => [
                'purchased' => 3,
                'follower' => 7,
                'subscriber' => 2,
            ],
            'local' => [
                'purchased' => 3,
                'follower' => 7,
                'subscriber' => 2,
            ],
        ];
        return $max[$this->appEnv][$param];
    }

}
