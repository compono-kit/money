<?php declare(strict_types=1);

namespace ComponoKit\Money\Utilities;

use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Money\Traits\AssertingCurrencies;

final class MoneyAggregator
{
	use AssertingCurrencies;

	public static function min( RepresentsMoney $firstMoney, RepresentsMoney ...$moneyCollection ): RepresentsMoney
	{
		$min = $firstMoney;

		foreach ( $moneyCollection as $money )
		{
			self::assertSameCurrency( $firstMoney, $money );

			if ( $money->lessThan( $min ) )
			{
				$min = $money;
			}
		}

		return $min;
	}

	public static function max( RepresentsMoney $firstMoney, RepresentsMoney ...$moneyCollection ): RepresentsMoney
	{
		$max = $firstMoney;

		foreach ( $moneyCollection as $money )
		{
			self::assertSameCurrency( $firstMoney, $money );

			if ( $money->greaterThan( $max ) )
			{
				$max = $money;
			}
		}

		return $max;
	}

	public static function avg( RepresentsMoney $firstMoney, RepresentsMoney ...$moneyCollection ): RepresentsMoney
	{
		return self::sum( $firstMoney, ...$moneyCollection )->divide( (float)(count( $moneyCollection ) + 1) );
	}

	public static function sum( RepresentsMoney $firstMoney, RepresentsMoney ...$moneyCollection ): RepresentsMoney
	{
		$summedMoney = $firstMoney;

		foreach ( $moneyCollection as $money )
		{
			self::assertSameCurrency( $firstMoney, $money );

			$summedMoney = $summedMoney->add( $money );
		}

		return $summedMoney;
	}
}
