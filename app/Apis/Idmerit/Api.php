<?php
namespace App\Apis\Idmerit;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;


//use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Models\User;
//use Psr\Http\Message\ResponseInterface;

// Singleton w/ 'factory'-type create method
class Api
{
    protected $endpoints = null;
    protected $token = null;
    //protected $base = null;

    private function __construct() {
        if (App::environment(['local', 'testing', 'staging'])) {
            $base = 'https://sandbox.idmvalidate.com';
        } else {
            // production!
            throw new Exception('production endoints TBD!');
            $base = '...';
        }
        $this->endpoints = [
            'token' => [
                'url' => "$base/token",
            ],
            'verify' => [
                'url' => "$base/verify",
            ],
            'status' => [
                'url' => "$base/verify",
            ],
        ];
        //$this->base = $base;
    }

    public static function create(array $config=[]) {
       static $_this = null;
        if ( is_null($_this) ) {
            $_this = new Api();
        }
       return $_this;
    }

    public function getToken() {
        $url = $this->endpoints['token']['url'];
        $clientId = env('IDMERIT_CLIENT_ID', null);
        $clientSecret = env('IDMERIT_CLIENT_SECRET', null);
        if ( !$clientId || !$clientSecret ) {
            throw new Exception('Missing ID or secret');
        }
        //$response = Http::dd()->withBasicAuth($clientId, $clientSecret)->post($url);
        $response = Http::asForm()->withBasicAuth($clientId, $clientSecret)
          ->post($url, [
              'grant_type' => 'client_credentials',
              'scope' => 'idmvalidate',
          ]);
        /*
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->withBasicAuth($clientId, $clientSecret)
          ->post($url, [
              'grant_type' => 'client_credentials',
              'scope' => 'idmvalidate',
          ]);
        */
        return $response;
    }

    public function doVerify(User $user) {
    }

    public function checkVerify(User $user) {
    }

}
