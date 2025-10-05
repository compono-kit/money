<?php declare(strict_types=1);

namespace ComponoKit\Money\Formatters;

use ComponoKit\Money\Interfaces\FormatsMoneyString;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter as BaseIntlMoneyFormatter;
use Money\Money;

class IntlDecimalFormatter implements FormatsMoneyString
{
	public function __construct( private readonly string $locale )
	{
	}

	public function formatString( RepresentsMoney $money ): string
	{
		return self::format( $money, $this->locale );
	}

	public static function format( RepresentsMoney $money, string $locale ): string
	{
		$numberFormatter = new \NumberFormatter( $locale, \NumberFormatter::DECIMAL );
		$moneyFormatter  = new BaseIntlMoneyFormatter( $numberFormatter, new ISOCurrencies() );

		return str_replace( "\xc2\xa0", ' ', $moneyFormatter->format( new Money( $money->getAmount(), new Currency( $money->getCurrencyCode() ) ) ) );
	}
}
