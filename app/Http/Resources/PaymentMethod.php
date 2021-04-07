<?php

namespace App\Http\Resources;

use App\Models\Financial\SegpayCard;
use Illuminate\Http\Resources\Json\JsonResource;
use stdClass;

class PaymentMethod extends JsonResource
{
    public static $types = [
        SegpayCard::class => 'card',
    ];

    public function toArray($request)
    {
        $resource = $this->resource->resource ?? new stdClass();
        // dd($resource);
        return [
            'id' => $this->id,
            'type' => static::$types[get_class($resource)] ?? null,
            'brand' => $resource->card_type ?? null,
            'nickname' => $resource->nickname ?? $this->name ?? null,
            'last_4' => $resource->last_4 ?? null,
            'created_at' => $this->created_at,
        ];
    }
}