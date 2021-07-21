<?php
namespace Tests\Unit;

use DB;
use Illuminate\Foundation\Testing\WithFaker;

use Carbon\Carbon;
use Tests\TestCase;

use App\Apis\Sendgrid\Api as SendgridApi;

class SendgridApiTest extends TestCase
{
    /**
     * @group sendgrid-api-unit
     * @group OFF-here0719
     */
    public function test_should_send_email()
    {
        $response = SendgridApi::send('d-c81aa70638ac40f5a33579bf425aa591', [
            //'subject' => 'Subject Override Ex',
            'to' => [
                'email' => 'peter+test1@peltronic.com', 
                'name' => 'Peter Test1',
            ],
            'dtdata' => [
                'display_name' => 'Joe Displayname', 
                'amount' => '$17.19',
            ],
        ]);
        $isSandbox = env('DEBUG_ENABLE_SENDGRID_SANDBOX_MODE', false);
        $this->assertEquals( $isSandbox?200:202, $response->statusCode() );
    }

}

