<?php declare(strict_types=1);

namespace ComponoKit\Money\Tests\Unit\Traits;

use ComponoKit\Money\Currency;
use ComponoKit\Money\Interfaces\RepresentsCurrency;

trait BuildingCurrencies
{
	private function buildEurCurrency(): RepresentsCurrency
	{
		return new Currency( 'EUR', '€', 100 );
	}

	private function buildUsdCurrency(): RepresentsCurrency
	{
		return new Currency( 'USD', '$', 100 );
	}
}
