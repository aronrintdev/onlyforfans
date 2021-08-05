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

    private function __construct(string $token=null) {
        if ( Config::get('idmerit.is_sandbox', false) ) {
            $base = 'https://sandbox.idmvalidate.com';
        } else {
            // production!
            $base = 'https://prod.idmvalidate.com';
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
        if ( !empty($token) ) {
            $this->token = $token;
        } else {
            $this->issueToken(); // call api to generate token and save it
        }
    }

    public static function create(string $token=null) {
       static $_this = null;
        if ( is_null($_this) ) {
            $_this = new Api($token);
        }
       return $_this;
    }

    public function issueToken() 
    {
        $clientId = Config::get('idmerit.client_id');
        $clientSecret = Config::get('idmerit.client_secret');
        $url = $this->endpoints['token']['url'];

        if ( !$clientId || !$clientSecret ) {
            throw new Exception('Missing ID or secret');
        }

        $response = Http::asForm()->withBasicAuth($clientId, $clientSecret)
          ->post($url, [
              'grant_type' => 'client_credentials',
              'scope' => 'idmvalidate',
          ]);
        $json = $response->json();
        if ( !array_key_exists('access_token', $json) ) {
            throw new Exception('issueToken() - could not retrieve token');
        }
        $this->token = $json['access_token']; // set token
        return $response; // return response
    }

    public function doVerify(array $attrs)
    {
        $url = $this->endpoints['verify']['url'];
        $response = Http::withToken($this->token)->post($url, $attrs);
        return $response;
    }

    public function checkVerify(string $uniqueID) 
    {
        $url = $this->endpoints['status']['url']."/$uniqueID";
        $response = Http::withToken($this->token)
            ->withHeaders([ 'Content-Type' => 'application/json' ])
            ->get($url);
        return $response;
    }

    public function getToken() {
        return $this->token;
    }
    public function hasToken() {
        return !empty($this->token);
    }

}
