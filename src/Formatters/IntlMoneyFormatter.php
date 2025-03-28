<?php declare(strict_types=1);

namespace Componium\Money\Formatters;

use Componium\Money\Interfaces\RepresentsMoney;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter as BaseIntlMoneyFormatter;
use Money\Money;

class IntlMoneyFormatter
{
	public static function format( RepresentsMoney $money, string $locale ): string
	{
		$numberFormatter = new \NumberFormatter( $locale, \NumberFormatter::CURRENCY );
		$moneyFormatter  = new BaseIntlMoneyFormatter( $numberFormatter, new ISOCurrencies() );

		return str_replace( "\xc2\xa0", ' ', $moneyFormatter->format( new Money( $money->getAmount(), new Currency( $money->getCurrencyCode() ) ) ) );
	}
}
