<?php

namespace App\Models\Traits;

use Money\Money;
use Money\Currency;
use NumberFormatter;
use Illuminate\Support\Facades\App;
use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
use Money\Currencies\BitcoinCurrencies;
use Money\Formatter\IntlMoneyFormatter;
use Money\Formatter\BitcoinMoneyFormatter;
use Money\Formatter\AggregateMoneyFormatter;
use Money\Parser\IntlLocalizedDecimalParser;

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
        $numberFormatter = new NumberFormatter(App::currentLocale() ?? 'en_US', NumberFormatter::CURRENCY);
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
        $numberFormatter = new NumberFormatter(App::currentLocale() ?? 'en_US', NumberFormatter::DECIMAL);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);
        return $moneyFormatter->format($money);
    }

    public static function parseMoneyDecimal($value, $currency): Money
    {
        $currencies = new ISOCurrencies();
        // $numberFormatter = new NumberFormatter(App::currentLocale() ?? 'en_US', NumberFormatter::CURRENCY);
        $moneyParser = new DecimalMoneyParser($currencies);

        if (is_numeric($value)) {
            $value = strval($value);
        }

        return $moneyParser->parse($value, new Currency($currency));
    }
}
