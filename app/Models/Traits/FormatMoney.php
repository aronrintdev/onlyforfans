<?php

namespace App\Models\Traits;

use Money\Money;
use NumberFormatter;
use Illuminate\Support\Facades\App;
use Money\Currencies\ISOCurrencies;
use Money\Currencies\BitcoinCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Formatter\BitcoinMoneyFormatter;
use Money\Formatter\AggregateMoneyFormatter;

trait FormatMoney
{
    /**
     * Formats Money object for display
     * @param Money $money
     * @return string
     */
    public static function formatMoney(Money $money): string
    {
        $currencies = new ISOCurrencies();
        $numberFormatter = new NumberFormatter(App::currentLocale(), NumberFormatter::CURRENCY);
        $intlFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);
        $bitcoinFormatter = new BitcoinMoneyFormatter(7, new BitcoinCurrencies());

        $moneyFormatter = new AggregateMoneyFormatter([
            'USD' => $intlFormatter,
            'XBT' => $bitcoinFormatter,
        ]);

        return $moneyFormatter->format($money);
    }

    public static function formatMoneyDecimal(Money $money): string
    {
        $currencies = new ISOCurrencies();
        $numberFormatter = new NumberFormatter(App::currentLocale(), NumberFormatter::DECIMAL);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);
        return $moneyFormatter->format($money);
    }
}