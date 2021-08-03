<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use DB;

use Tests\TestCase;
use Database\Seeders\TestDatabaseSeeder;
use App\Models\Referral;
use App\Models\User;

class RestReferralTest extends TestCase
{
    use WithFaker;
    public function test_can_list_referrals()
    {
        $user = User::has('referrals','>=',1)->firstOrFail();

        $response = $this->actingAs($user)->ajaxJSON('GET', route('referrals.index'), [
            //'user_id' => $user->id,
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links',
            'meta' => [ 'current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total', ],
        ]);

        $content = json_decode($response->content());
        $this->assertNotNull($content->data);
        $this->assertGreaterThan(0, count($content->data));
        collect($content->data)->each( function($c) use(&$user) { // all belong to owner
            $this->assertEquals($user->id, $c->user_id);
        });
    }
}
