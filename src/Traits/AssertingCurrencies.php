<?php declare(strict_types=1);

namespace ComponoKit\Money\Traits;

use ComponoKit\Money\Exceptions\CurrencyMismatchException;
use ComponoKit\Money\Interfaces\RepresentsMoney;

trait AssertingCurrencies
{
	private static function assertSameCurrency( RepresentsMoney $money, RepresentsMoney $moneyToCompareWith ): void
	{
		if ( !$moneyToCompareWith->hasSameCurrency( $money ) )
		{
			throw new CurrencyMismatchException( sprintf( 'Currency mismatch: %s != %s', $moneyToCompareWith->getCurrencyCode(), $money->getCurrencyCode() ) );
		}
	}
}
