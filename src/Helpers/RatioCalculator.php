<?php declare(strict_types=1);

namespace ComponoKit\Money\Helpers;

use ComponoKit\Money\Exceptions\InvalidRatioException;

final class RatioCalculator
{
	private function __construct()
	{
	}

	/**
	 * @param int $amount
	 * @param int $numberOfTargets
	 *
	 * @return int[]
	 */
	public static function allocateToTargets( int $amount, int $numberOfTargets ): array
	{
		return self::allocateByRatios( $amount, array_fill( 0, $numberOfTargets, 1 ) );
	}

	/**
	 * @param int   $amount
	 * @param int[] $ratios
	 *
	 * @return int[]
	 */
	public static function allocateByRatios( int $amount, array $ratios ): array
	{
		$totalRatio = array_sum( $ratios );

		if ( $totalRatio <= 0 )
		{
			throw new InvalidRatioException( 'Total ratio must be greater than 0' );
		}

		$remaining = $amount;
		$shares    = [];

		foreach ( $ratios as $key => $ratio )
		{
			if ( $ratio < 0 )
			{
				throw new InvalidRatioException( 'Ratio must be equal or greater than 0' );
			}

			$share          = (int)floor( $amount * $ratio / $totalRatio );
			$shares[ $key ] = $share;
			$remaining      -= $share;
		}

		$fractions = self::calculateFractions( $ratios, $amount, $totalRatio );

		return self::distributeRemainder( $remaining, $fractions, $shares );
	}

	private static function calculateFractions( array $ratios, int $amount, int $totalRatio ): array
	{
		$fractions = [];
		foreach ( $ratios as $key => $ratio )
		{
			$share             = ($amount * $ratio / $totalRatio);
			$fractions[ $key ] = $share - floor( $share );
		}

		return $fractions;
	}

	private static function distributeRemainder( int $remaining, array $fractions, array $shares ): array
	{
		while ( $remaining > 0 )
		{
			$index = self::getIndexOfMaxFraction( $fractions, $shares );
			$shares[ $index ]++;
			$remaining--;
			unset( $fractions[ $index ] );
		}

		return $shares;
	}

	private static function getIndexOfMaxFraction( array $fractions, array $shares ): int
	{
		if ( !$fractions )
		{
			return array_key_first( $shares );
		}

		return array_keys( $fractions, max( $fractions ) )[0];
	}
}
