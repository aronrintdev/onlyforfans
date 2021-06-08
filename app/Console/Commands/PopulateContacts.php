<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Mycontact;
use App\Events\MessageSentEvent;
use App\Events\MessagePublishedEvent;

class PopulateContacts extends Command
{
    protected $signature = 'populate:contacts';
    protected $description = 'Fill in contacts with current subcribers';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = date("Y-m-d H:i", strtotime(Carbon::now('UTC')));

        $users = User::get();

        $this->info('PopulateContacts - found '.$users->count().' users...');

        $users->each( function($u) {
            if ( !$u->timeline ) {
                return;
            }
            $subscribee = $u;

            $this->info('PopulateContacts - found '.$u->timeline->subscribers->count().' subscribers for user '.$subscribee->username.'...');

            $u->timeline->followers->each( function($subscriber) use(&$subscribee) { // user followers for now as subcribers is FUBAR
            //$u->timeline->subscribers->each( function($subscriber) use(&$subscribee) {

                $this->info('PopulateContacts - add contact '.$subscriber->username.' for user '.$subscribee->username.'...');

                $exists = Mycontact::where('owner_id', $subscribee->id)->where('contact_id', $subscriber->id)->count();
                if ( !$exists ) {
                    // add to subcribee's contacts
                    $mc1 = Mycontact::create([
                        'owner_id' => $subscribee->id, // subcribee
                        'contact_id' => $subscriber->id, // subcriber
                    ]);
                }

                $exists = Mycontact::where('owner_id', $subscriber->id)->where('contact_id', $subscribee->id)->count();
                if ( !$exists ) {
                    // add to subcriber's contacts
                    $mc2 = Mycontact::create([
                        'contact_id' => $subscribee->id, // subcribee
                        'owner_id' => $subscriber->id, // subcriber
                    ]);
                }
            });
        });

    } // handle()
}
