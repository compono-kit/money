<?php declare(strict_types=1);

namespace ComponoKit\Money;

use ComponoKit\Money\Exceptions\AmountOutOfRangeException;
use ComponoKit\Money\Exceptions\InvalidRatioException;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsExtractedPercentage;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Money\Traits\AssertingCurrencies;

class Money implements RepresentsMoney
{
	use AssertingCurrencies;

	public function __construct( private readonly int $amount, private readonly RepresentsCurrency $currency )
	{
	}

	public function getAmount(): int
	{
		return $this->amount;
	}

	public function getCurrencyCode(): string
	{
		return $this->getCurrency()->getIsoCode();
	}

	public static function fromFloat( float $amount, RepresentsCurrency $currency, \RoundingMode $roundingMode = \RoundingMode::HalfAwayFromZero ): static
	{
		return new static( self::toAmount( round( $amount * $currency->getMinorUnitFactor(), 0, $roundingMode ) ), $currency );
	}

	public function getCurrency(): RepresentsCurrency
	{
		return $this->currency;
	}

	public function add( RepresentsMoney $other ): static
	{
		self::assertSameCurrency( $this, $other );

		return new static( self::guardIntegerResult( $this->amount + $other->getAmount() ), $this->getCurrency() );
	}

	public function subtract( RepresentsMoney $other ): static
	{
		self::assertSameCurrency( $this, $other );

		return new static( self::guardIntegerResult( $this->amount - $other->getAmount() ), $this->getCurrency() );
	}

	public function multiply( float $factor, \RoundingMode $roundingMode = \RoundingMode::HalfAwayFromZero ): static
	{
		return new static( self::toAmount( round( $this->amount * $factor, 0, $roundingMode ) ), $this->getCurrency() );
	}

	public function divide( float $divisor, \RoundingMode $roundingMode = \RoundingMode::HalfAwayFromZero ): static
	{
		return new static( self::toAmount( round( $this->amount / $divisor, 0, $roundingMode ) ), $this->getCurrency() );
	}

	public function mod( RepresentsMoney $money ): static
	{
		self::assertSameCurrency( $this, $money );

		return new static( $this->amount % $money->getAmount(), $this->getCurrency() );
	}

	public function absolute(): static
	{
		return new static( self::guardIntegerResult( abs( $this->amount ) ), $this->getCurrency() );
	}

	public function negate(): static
	{
		return new static( self::guardIntegerResult( -1 * $this->amount ), $this->getCurrency() );
	}

	public function ratioOf( RepresentsMoney $money ): float
	{
		self::assertSameCurrency( $this, $money );

		if ( $money->isZero() )
		{
			throw new InvalidRatioException( "Ratio can't be 0" );
		}

		return $this->amount / $money->getAmount();
	}

	public function isZero(): bool
	{
		return $this->amount === 0;
	}

	public function isPositive(): bool
	{
		return $this->amount > 0;
	}

	public function isNegative(): bool
	{
		return $this->amount < 0;
	}

	/**
	 * @return \Iterator<int,static>
	 */
	public function allocateToTargets( int $numberOfTargets ): \Iterator
	{
		if ( $numberOfTargets <= 0 )
		{
			throw new InvalidRatioException( 'Total ratio must be greater than 0' );
		}

		foreach ( $this->allocateAmountByRatios( $this->amount, array_fill( 0, $numberOfTargets, 1 ) ) as $share )
		{
			yield new static( $share, $this->getCurrency() );
		}
	}

	/**
	 * @param int[] $ratios
	 *
	 * @return \Iterator<int,static>
	 */
	public function allocateByRatios( array $ratios ): \Iterator
	{
		foreach ( $this->allocateAmountByRatios( $this->amount, $ratios ) as $share )
		{
			yield new static( $share, $this->getCurrency() );
		}
	}

	public function extractPercentage( float $percentage, \RoundingMode $roundingMode = \RoundingMode::HalfAwayFromZero ): RepresentsExtractedPercentage
	{
		$percentageAmount = new static(
			self::toAmount( round( $this->amount / (100 + $percentage) * $percentage, 0, $roundingMode ) ),
			$this->getCurrency()
		);

		return new ExtractedPercentage( $percentageAmount, $this->subtract( $percentageAmount ) );
	}

	public function equals( RepresentsMoney $other ): bool
	{
		self::assertSameCurrency( $this, $other );

		return $this->compareTo( $other ) === 0;
	}

	public function greaterThan( RepresentsMoney $other ): bool
	{
		self::assertSameCurrency( $this, $other );

		return $this->compareTo( $other ) === 1;
	}

	public function greaterThanOrEqual( RepresentsMoney $other ): bool
	{
		self::assertSameCurrency( $this, $other );

		return $this->compareTo( $other ) >= 0;
	}

	public function lessThan( RepresentsMoney $other ): bool
	{
		self::assertSameCurrency( $this, $other );

		return $this->compareTo( $other ) === -1;
	}

	public function lessThanOrEqual( RepresentsMoney $other ): bool
	{
		self::assertSameCurrency( $this, $other );

		return $this->compareTo( $other ) <= 0;
	}

	public function hasSameCurrency( RepresentsMoney $money ): bool
	{
		return $this->currency->equals( $money->getCurrency() );
	}

	public function jsonSerialize(): array
	{
		return [
			'amount'   => $this->amount,
			'currency' => [
				'iso-code'          => $this->currency->getIsoCode(),
				'symbol'            => $this->currency->getSymbol(),
				'minor-unit-factor' => $this->currency->getMinorUnitFactor(),
				'minor-unit'        => $this->currency->getMinorUnit(),
			],
		];
	}

	private static function toAmount( float $value ): int
	{
		if ( !is_finite( $value ) || $value < PHP_INT_MIN || $value >= PHP_INT_MAX )
		{
			throw new AmountOutOfRangeException( 'Amount is out of the integer range: ' . $value );
		}

		return (int)$value;
	}

	private static function guardIntegerResult( int|float $value ): int
	{
		if ( is_float( $value ) )
		{
			throw new AmountOutOfRangeException( 'Amount is out of the integer range: ' . $value );
		}

		return $value;
	}

	private function allocateAmountByRatios( int $amount, array $ratios ): array
	{
		foreach ( $ratios as $ratio )
		{
			if ( !is_int( $ratio ) || $ratio < 0 )
			{
				throw new InvalidRatioException( 'Ratio must be an integer equal or greater than 0' );
			}
		}

		$totalRatio = array_sum( $ratios );

		if ( $totalRatio <= 0 )
		{
			throw new InvalidRatioException( 'Total ratio must be greater than 0' );
		}

		$remaining = $amount;
		$shares    = [];

		foreach ( $ratios as $key => $ratio )
		{
			$share          = (int)floor( $amount * $ratio / $totalRatio );
			$shares[ $key ] = $share;
			$remaining      -= $share;
		}

		$fractions = $this->calculateFractions( $ratios, $amount, $totalRatio );

		return $this->distributeRemainder( $remaining, $fractions, $shares );
	}

	private function calculateFractions( array $ratios, int $amount, int $totalRatio ): array
	{
		$fractions = [];
		foreach ( $ratios as $key => $ratio )
		{
			$share             = ($amount * $ratio / $totalRatio);
			$fractions[ $key ] = $share - floor( $share );
		}

		return $fractions;
	}

	private function distributeRemainder( int $remaining, array $fractions, array $shares ): array
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

	private function getIndexOfMaxFraction( array $fractions, array $shares ): int
	{
		if ( !$fractions )
		{
			return array_key_first( $shares );
		}

		return array_keys( $fractions, max( $fractions ) )[0];
	}

	private function compareTo( RepresentsMoney $other ): int
	{
		return $this->amount <=> $other->getAmount();
	}
}
