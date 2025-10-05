<?php declare(strict_types=1);

namespace ComponoKit\Money\Interfaces;

interface RepresentsMoney extends \JsonSerializable, \Stringable
{
	public function getAmount(): int;

	public function getCurrency(): RepresentsCurrency;

	public function add( RepresentsMoney $other ): RepresentsMoney;

	public function subtract( RepresentsMoney $other ): RepresentsMoney;

	public function multiply( float $factor, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentsMoney;

	public function divide( float $factor, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentsMoney;

	public function mod( RepresentsMoney $money ): RepresentsMoney;

	public function absolute(): RepresentsMoney;

	public function negate(): RepresentsMoney;

	public function ratioOf( RepresentsMoney $money ): float;

	public function isZero(): bool;

	public function isPositive(): bool;

	public function isNegative(): bool;

	/**
	 * @return \Iterator<int,RepresentsMoney>
	 */
	public function allocateToTargets( int $numberOfTargets ): \Iterator;

	/**
	 * @param array|int[] $ratios
	 *
	 * @return \Iterator<int,RepresentsMoney>
	 */
	public function allocateByRatios( array $ratios ): \Iterator;

	public function extractPercentage( float $percentage, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentExtractedPercentage;

	public function equals( RepresentsMoney $other ): bool;

	public function greaterThan( RepresentsMoney $other ): bool;

	public function greaterThanOrEqual( RepresentsMoney $other ): bool;

	public function lessThan( RepresentsMoney $other ): bool;

	public function lessThanOrEqual( RepresentsMoney $other ): bool;

	public function hasSameCurrency( RepresentsMoney $money ): bool;
}
