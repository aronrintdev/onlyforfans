<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class PaymentMethodCollection extends ResourceCollection
{
    public $collects = PaymentMethod::class;

    public function toArray($request)
    {
        $default = Auth::user()->settings->cattrs['default_payment_method'] ?? null;
        return [
            'data' => $this->collection,
            'default' => $default,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}