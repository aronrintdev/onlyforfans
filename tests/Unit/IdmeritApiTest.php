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
     * @group idmerit-api-unit
     * @group NO-regression
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
     * @group idmerit-api-unit
     * @group NO-regression
     */
    public function test_should_send_verify_request()
    {
        $userAttrs = [
	        'mobile' => '+94777878905',
	        'name' => 'Dilshan Edirisuriya',
	        'country' => 'LK',
	        'requestID' => 'additional information',
	        'dateOfBirth' => '19901231',
	        //'callbackURL': 'https://devapp.idmvalidate.com/verify/endpoint/success'
        ];
        $api = IdMeritApi::create();
        if ( !$api->hasToken ) {
            $response = $api->issueToken();
            $this->assertEquals( 200, $response->status() );
        }

        // --

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
        $this->assertNotNull($json['uniqueID']);
        //dd($json);
    }

    /**
     * @group idmerit-api-unit
     * @group NO-regression
     // %NOTE: Assumes the 'hardcoded' unique IDs already exist in our test sandbox!
     */
    public function test_should_poll_verify_request_status()
    {
        $api = IdMeritApi::create();
        if ( !$api->hasToken ) {
            $response = $api->issueToken();
            $this->assertEquals( 200, $response->status() );
        }

        // --

        $uniqueIDs = [
            'passed' => 'da8dc894-e7ee-4d64-a9de-8351deec324b',
            'failed' => '0057899f-443d-4623-9d4e-f621ba6663d8',
            'pending' => 'a8a60ca5-432e-4363-86b5-5bd91231b60b',
        ];

        // -- Test pending --

        $response = $api->checkVerify($uniqueIDs['pending']);
        $this->assertEquals( 200, $response->status() );
        $json = $response->json();

        $this->assertArrayHasKey('status', $json);
        $this->assertArrayHasKey('requestId', $json);
        $this->assertArrayHasKey('identifier', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('dateOfBirth', $json);
        $this->assertArrayHasKey('documentType', $json);
        $this->assertArrayHasKey('country', $json);
        $this->assertArrayHasKey('scanImage', $json);
        $this->assertArrayHasKey('selfieImage', $json);
        $this->assertArrayHasKey('mobile', $json);
        $this->assertArrayHasKey('countryCode', $json);
        $this->assertArrayHasKey('requestedTime', $json);
        $this->assertArrayHasKey('validatedTime', $json);
        $this->assertArrayHasKey('faceMatches', $json);
        $this->assertArrayHasKey('liveness', $json);
        $this->assertArrayHasKey('documentScore', $json);
        $this->assertArrayHasKey('riskFactor', $json);
        $this->assertArrayHasKey('nameScore', $json);
        $this->assertArrayHasKey('latitude', $json);
        $this->assertArrayHasKey('longitude', $json);
        $this->assertArrayHasKey('userAgent', $json);
        $this->assertArrayHasKey('dobScore', $json);
        $this->assertArrayHasKey('deviceId', $json);
        $this->assertArrayHasKey('barcodeMap', $json);

        $this->assertEquals('in_progress', $json['status']);

        // -- Test failed --

        $response = $api->checkVerify($uniqueIDs['failed']);
        $this->assertEquals( 200, $response->status() );
        $json = $response->json();
        $this->assertEquals('failed', $json['status']);


        // -- Test passed/verified --

        $response = $api->checkVerify($uniqueIDs['passed']);
        $this->assertEquals( 200, $response->status() );
        $json = $response->json();
        $this->assertEquals('verified', $json['status']);


        //dd($json);
    }

}

