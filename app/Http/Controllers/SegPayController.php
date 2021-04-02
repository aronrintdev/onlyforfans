<?php

namespace App\Http\Controllers;

use App\Helpers\Purchasable as PurchasableHelpers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SegPayController extends Controller
{
    /**
     * Generate the pay page Url
     * @return void
     */
    public function generatePayPageUrl(Request $request)
    {
        if (isset($request->item)) {
            $item = PurchasableHelpers::getPurchasableItem($request->item);
            $price = $item->formatMoneyDecimal($item->price);
        } else {
            $price = $request->price;
        }

        if (!isset($price)) {
            return [ 'message' => 'Price or item is required' ];
        }

        $client = new Client();
        $response = $client->request('GET', Config::get('segpay.dynamicTransUrl'), [
            'query' => [ 'value' => $price, ],
        ]);

        $priceEncode = simplexml_load_string($response->getBody(), 'SimpleXMLElement', LIBXML_NOCDATA)->__toString();

        $baseUrl = Config::get('segpay.secure') ? 'https://' : 'http://';
        $baseUrl .= Config::get('segpay.baseUrl');
        $packageId = Config::get('segpay.packageId');
        $pricePointId = Config::get('segpay.pricePointId');
        $appName = Config::get('app.name');
        $env = Config::get('app.env');

        $xEticketid = "{$packageId}:{$pricePointId}";
        $description = urlencode('All Fans Purchase');
        $save = ($request->save) ? '1' : '0';
        $userId = Auth::user()->getKey();

        $url = "{$baseUrl}?x-eticketid={$xEticketid}&amount={$price}&dynamictrans={$priceEncode}&dynamicdesc={$description}&app={$appName}&env={$env}&user_id={$userId}&save={$save}";
        if (isset($item)) {
            $url .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }

        // Generate Hash signature
        $secret = Config::get('segpay.secret');
        $body = "&app={$appName}&env={$env}&price={$price}&user_id={$userId}&save={$save}";
        if (isset($item)) {
            $body .= "&item_id={$item->getKey()}&item_type={$item->getMorphString()}";
        }
        $hash = hash_hmac('sha256', $body, $secret, false);
        $url .= "&REF1={$hash}";

        return $url;
    }
}
