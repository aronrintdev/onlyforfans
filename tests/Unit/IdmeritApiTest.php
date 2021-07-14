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
     * @group OFF-here0714
     */
    public function test_should_get_token()
    {
        $api = IdMeritApi::create();
        $response = $api->issueToken();
        $this->assertEquals( 200, $response->status() );
        $json = $response->json();
        //dd( $json );
        $this->assertArrayHasKey('access_token', $json);
        $this->assertArrayHasKey('token_type', $json);
        $this->assertArrayHasKey('expires_in', $json);
        //dd( $response );
    }

    /**
     * @group idmerit-api
     * @group here0714
     */
    public function test_should_send_verify_request()
    {
        $userAttrs = [
	        "mobile" => "+94777878905",
	        "name" => "Dilshan Edirisuriya",
	        "country" => "LK",
	        "requestID" => "additional information",
	        "dateOfBirth" => "19901231",
	        //"callbackURL": "https://devapp.idmvalidate.com/verify/endpoint/success"
        ];
        $api = IdMeritApi::create();
        $response = $api->issueToken();
        $this->assertEquals( 200, $response->status() );

        $response = $api->doVerify($userAttrs);
        //dd( $response );
        $this->assertEquals( 200, $response->status() );
        $json = $response->json();
        $this->assertArrayHasKey('requestID', $json);
        $this->assertArrayHasKey('callbackURL', $json);
        $this->assertArrayHasKey('uniqueID', $json);
        $this->assertArrayHasKey('status', $json);
        $this->assertArrayHasKey('documentType', $json);
        $this->assertArrayHasKey('barcodeType', $json);
        $this->assertArrayHasKey('skipBarcode', $json);
        $this->assertArrayHasKey('skipLiveness', $json);
        $this->assertArrayHasKey('qrCode', $json);
        $this->assertArrayHasKey('redirectURL', $json);
        $this->assertEquals('in_progress', $json['status']);
        //dd($json);
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
