<?php
namespace App\Enums;

use App\Interfaces\Selectable;

// aka FanledgerTypeEnum
class CampaignTypeEnum extends SmartEnum implements Selectable
{
    const DISCOUNT = 'discount';
    const TRIAL = 'trial';

    public static $keymap = [
        self::DISCOUNT => 'Discount',
        self::TRIAL => 'trial',
    ];
}
