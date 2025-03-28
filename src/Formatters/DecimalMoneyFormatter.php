<?php declare(strict_types=1);

namespace Componium\Money\Formatters;

use Componium\Money\Interfaces\RepresentsMoney;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter as BaseDecimalMoneyFormatter;
use Money\Money;

class DecimalMoneyFormatter
{
	public static function format( RepresentsMoney $money ): string
	{
		return new BaseDecimalMoneyFormatter( new ISOCurrencies() )->format(
			new Money( $money->getAmount(), new Currency( $money->getCurrencyCode() ) )
		);
	}
}
