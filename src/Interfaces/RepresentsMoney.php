<?php declare(strict_types=1);

namespace Componium\Money\Interfaces;

interface RepresentsMoney
{
	public function getAmount(): int;

	public function getCurrencyCode(): string;

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
	 * @param int $targetCount
	 *
	 * @return RepresentsMoney[]
	 */
	public function allocateToTargets( int $targetCount ): array;

	/**
	 * @param array|int[] $ratios
	 *
	 * @return RepresentsMoney[]
	 */
	public function allocateByRatios( array $ratios ): array;

	public function extractPercentage( float $percentage, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentExtractedPercentage;

	public function equals( RepresentsMoney $other ): bool;

	public function greaterThan( RepresentsMoney $other ): bool;

	public function greaterThanOrEqual( RepresentsMoney $other ): bool;

	public function lessThan( RepresentsMoney $other ): bool;

	public function lessThanOrEqual( RepresentsMoney $other ): bool;

	public function hasSameCurrency( RepresentsMoney $money ): bool;
}
