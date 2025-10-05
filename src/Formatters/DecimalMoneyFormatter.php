<?php declare(strict_types=1);

namespace ComponoKit\Money\Formatters;

use ComponoKit\Money\Interfaces\FormatsMoneyString;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter as BaseDecimalMoneyFormatter;
use Money\Money;

class DecimalMoneyFormatter implements FormatsMoneyString
{
	public function formatString( RepresentsMoney $money ): string
	{
		return self::format( $money );
	}

	public static function format( RepresentsMoney $money ): string
	{
		return new BaseDecimalMoneyFormatter( new ISOCurrencies() )->format(
			new Money( $money->getAmount(), new Currency( $money->getCurrencyCode() ) )
		);
	}
}
