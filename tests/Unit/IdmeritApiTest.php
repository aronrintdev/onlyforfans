<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;

use Carbon\Carbon;
use Tests\TestCase;

use App\Apis\IdMerit\Api as IdMeritApi;

class IdmeritApiTest extends TestCase
{
    /**
     * @group idmerit-api
     * @group here0714
     */
    public function test_get_token()
    {
        $api = IdMeritApi::create();
        $response = $api->getToken();
        $this->assertEquals( 200, $response->status() );
        $json = $response->json();
        //dd( $json );
        $this->assertArrayHasKey('access_token', $json);
        $this->assertArrayHasKey('token_type', $json);
        $this->assertArrayHasKey('expires_in', $json);
        //dd( $response );
    }

    /**
     * @group story-model
     */
    public function test_basic_ts_integrity()
    {
        $stories = Story::get();
        $stories->each( function($s) {
            $s->storyqueues->each( function($sq) use(&$s) {
                $this->assertEquals($s->timeline->id, $sq->timeline->id);
                $this->assertEquals($s->created_at, $sq->created_at);
            });
        });
    }

}
