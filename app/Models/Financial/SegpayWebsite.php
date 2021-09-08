<?php

namespace App\Models\Financial;

use Config;
use App\Models\User;
use App\Models\Timeline;
use Illuminate\Support\Str;
use App\Models\Traits\UsesUuid;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;

/**
 * @property string $id
 * @property string $timeline_id - What timeline this website is associated with
 *
 * @property int    $base_approved_id
 * @property string $url               - API required
 * @property string $username          - API required
 * @property string $password          - API required
 * @property string $access_notes
 * @property string $support_email     - API required
 * @property string $tech_email
 * @property string $faq_link
 * @property string $help_link
 *
 * @property Carbon|null $sent_at       - When the API call was made to segpay
 * @property Carbon|null $failed_at     - When the API call was made to segpay
 * @property int|null    $response_code - The response code from the API call
 * @property Collection  $response_body - The response from the API call
 * @property Collection  $notes         - Optional Admin Notes
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property User $owner
 * @property Timeline $timeline
 *
 * @package App\Models\Financial
 */
class SegpayWebsite extends Model
{
    use UsesUuid, SoftDeletes;

    protected $connection = 'financial';
    protected $table = 'segpay_websites';

    protected $guarded = [];

    protected $casts = [
        'username' => 'encrypted',
        'password' => 'encrypted',
        'response_body' => 'collection',
        'notes'    => 'collection',
    ];

    /* ------------------------------ Relations ----------------------------- */
    #region Relations
    /**
     * Timeline linked to this website
     */
    public function timeline()
    {
        return $this->belongsTo(Timeline::class);
    }

    #endregion

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    /**
     * Get the SegpayWebsite url for a specific timeline
     * @param Timeline $timeline
     * @return string
     */
    public static function urlFor(Timeline $timeline): string
    {
        return static::firstOrCreateFor($timeline)->url;
    }

    /**
     * Finds first or creates and sends api request for timeline
     * @param Timeline $timeline
     * @return SegpayWebsite
     */
    public static function firstOrCreateFor(Timeline $timeline)
    {
        if (Config::get('segpay.websites.useSlug', false)) {
            $url = Config::get('segpay.websites.baseUrl') . "/" . $timeline->slug;
        } else {
            $url = Config::get('segpay.websites.baseUrl') . "/u/" . $timeline->id;
        }
        $segpayWebsite = SegpayWebsite::where('timeline_id', $timeline->getKey())
            ->where('url', $url)
            ->whereNull('failed_at')
            ->first();
        if (!isset($segpayWebsite)) {
            $segpayWebsite = static::createAndSendFor($timeline);
        }
        return $segpayWebsite;
    }

    /**
     * Creates and then sends api call for a specific timeline
     */
    public static function createAndSendFor(Timeline $timeline)
    {
        $segpayWebsite = SegpayWebsite::createFor($timeline);
        $segpayWebsite->send();
        return $segpayWebsite;
    }

    /**
     * Creates SegpayWebsite for a specific timeline
     */
    public static function createFor(Timeline $timeline): SegpayWebsite
    {
        if (Config::get('segpay.websites.useSlug', false)) {
            $url = Config::get('segpay.websites.baseUrl') . "/" . $timeline->slug;
        } else {
            $url = Config::get('segpay.websites.baseUrl') . "/u/" . $timeline->id;
        }
        $username = Str::random(50);
        $password = Str::random(50);
        return SegpayWebsite::create([
            'timeline_id'      => $timeline->getKey(),
            'base_approved_id' => Config::get('segpay.websites.baseApprovedId'),
            'url'              => $url,
            'username'         => $username,
            'password'         => $password,
            'access_notes'     => null,
            'support_email'    => Config::get('segpay.websites.supportEmail'),
            'tech_email'       => Config::get('segpay.websites.techEmail'),
            'faq_link'         => Config::get('segpay.websites.faqLink'),
            'help_link'        => Config::get('segpay.websites.helpLink'),
        ]);
    }

    /**
     * Sends the API call
     */
    public function send()
    {
        $endpoint = Config::get('segpay.websites.endpoint');
        $body = [
            'BaseApprovedId' => $this->base_approved_id,
            'Url' => $this->url,
            'UserName' => $this->username,
            'Password' => $this->password,
            'SupportEmail' => $this->support_email,
        ];

        $optionals = [ 'access_notes', 'tech_email', 'faq_link', 'help_link' ];
        foreach($optionals as $optional) {
            if (isset($this->{$optional})) {
                $body[Str::studly($optional)] = $this->{$optional};
            }
        }
        $query = [
            'query' => [
                'test' => Config::get('segpay.websites.testMode'),
            ],
        ];
        $headers = [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Basic ' . base64_encode(Config::get('segpay.userId') . ':' . Config::get('segpay.accessKey')),
        ];

        try {
            $client = new Client();
            $response = $client->request('POST', $endpoint, [
                'headers' => $headers,
                'query' => $query,
                'body' => json_encode($body),
            ]);
            $this->response_code = $response->getStatusCode();
            $this->response_body = new Collection(json_decode($response->getBody(), true));
            $this->sent_at = Carbon::now();
        } catch (RequestException $e) {
            $response = $e->getResponse();
            Log::warning('SegpayWebsite send return with error code', [
                'endpoint' => $endpoint,
                'query' => $query,
                'headers' => $headers,
                'segpay_websites_id' => $this->getKey(),
                'status_code' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true),
            ]);
            $this->response_code = $response->getStatusCode();
            $this->response_body = new Collection(json_decode($response->getBody(), true));
            $this->sent_at = Carbon::now();
            $this->failed_at = Carbon::now();
        }

        $this->save();
        return $this;
    }

    #endregion
}
