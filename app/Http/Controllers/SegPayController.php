<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class SegPayController extends Controller
{
    /**
     * Generate the pay page Url
     * @return void
     */
    public function generatePayPageUrl(Request $request)
    {
        if (isset($request->item)) {
            //
        } else {
            $price = $request->price;
        }

        if (!isset($price)) {
            return [ 'message' => 'Price or item is required' ];
        }

        $client = new Client();
        $response = $client->request('GET', 'https://srs.segpay.com/PricingHash/PricingHash.svc/GetDynamicTrans', [
            'query' => [ 'value' => $price, ],
        ]);

        $priceEncode = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA)->__toString();

        // TODO: Move these to configuration
        $baseUrl = 'https://secure2.segpay.com/billing/poset.cgi';
        $packageID = '199225';
        $pricePointId = '26943';

        $url = "{$baseUrl}?x-eticketid={$packageID}:{$pricePointId}&amount={$price}&dynamictrans={$priceEncode}&dynamicdesc=All+Fans+Purchase";

        return $url;
    }
}
