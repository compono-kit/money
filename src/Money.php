<?php declare(strict_types=1);

namespace ComponoKit\Money;

use ComponoKit\Money\Exceptions\InvalidRatioException;
use ComponoKit\Money\Exceptions\InvalidRoundingModeException;
use ComponoKit\Money\Helpers\AmountAllocator;
use ComponoKit\Money\Interfaces\RepresentExtractedPercentage;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Money\Traits\AssertingCurrencies;

class Money implements RepresentsMoney
{
	use AssertingCurrencies;

	/** @var int[] */
	private static array $roundingModes = [
		PHP_ROUND_HALF_UP   => 1,
		PHP_ROUND_HALF_DOWN => 1,
		PHP_ROUND_HALF_EVEN => 1,
		PHP_ROUND_HALF_ODD  => 1,
	];

	public function __construct( private int $amount, private RepresentsCurrency $currency )
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

	public static function fromFloat( float $amount, RepresentsCurrency $currency, int $roundingMode = PHP_ROUND_HALF_UP ): static
	{
		return new static( (int)round( $amount * $currency->getMinorUnitFactor(), 0, $roundingMode ), $currency );
	}

	public function getCurrency(): RepresentsCurrency
	{
		return $this->currency;
	}



	public function add( RepresentsMoney $other ): RepresentsMoney
	{
		self::assertSameCurrency( $this, $other );

		return new static( $this->getAmount() + $other->getAmount(), $this->getCurrency() );
	}

	public function subtract( RepresentsMoney $other ): RepresentsMoney
	{
		self::assertSameCurrency( $this, $other );

		return new static( $this->getAmount() - $other->getAmount(), $this->getCurrency() );
	}

	public function multiply( float $factor, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentsMoney
	{
		$this->guardRoundingModeIsValid( $roundingMode );

		return new static( (int)round( $this->getAmount() * $factor, 0, $roundingMode ), $this->getCurrency() );
	}

	public function divide( float $divisor, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentsMoney
	{
		$this->guardRoundingModeIsValid( $roundingMode );

		return new static( (int)round( $this->getAmount() / $divisor, 0, $roundingMode ), $this->getCurrency() );
	}

	public function mod( RepresentsMoney $money ): RepresentsMoney
	{
		self::assertSameCurrency( $this, $money );
		
		return new static( $this->getAmount() % $money->getAmount(), $this->getCurrency() );
	}

	public function absolute(): RepresentsMoney
	{
		return new static( (int)round( abs( $this->getAmount() ) ), $this->getCurrency() );
	}

	public function negate(): RepresentsMoney
	{
		return new static( -1 * $this->getAmount(), $this->getCurrency() );
	}

	public function ratioOf( RepresentsMoney $money ): float
	{
		self::assertSameCurrency( $this, $money );
		
		if ( $money->isZero() )
		{
			throw new InvalidRatioException( "Ratio can't be 0" );
		}

		return $this->getAmount() / $money->getAmount();
	}

	public function isZero(): bool
	{
		return $this->getAmount() === 0;
	}

	public function isPositive(): bool
	{
		return $this->getAmount() > 0;
	}

	public function isNegative(): bool
	{
		return $this->getAmount() < 0;
	}

	/**
	 * @param int $numberOfTargets
	 *
	 * @return \Iterator<int,RepresentsMoney>
	 */
	public function allocateToTargets( int $numberOfTargets ): \Iterator
	{
		foreach ( AmountAllocator::allocateToTargets( $this->getAmount(), $numberOfTargets ) as $share )
		{
			yield new static( $share, $this->getCurrency() );
		}
	}

	/**
	 * @param array|int[] $ratios
	 *
	 * @return \Iterator<int,RepresentsMoney>
	 */
	public function allocateByRatios( array $ratios ): \Iterator
	{
		foreach ( AmountAllocator::allocateByRatios( $this->getAmount(), $ratios ) as $share )
		{
			yield new static( $share, $this->getCurrency() );
		}
	}

	public function extractPercentage( float $percentage, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentExtractedPercentage
	{
		$this->guardRoundingModeIsValid( $roundingMode );

		$percentageAmount = new self(
			(int)round( $this->getAmount() / (100 + $percentage) * $percentage, 0, $roundingMode ),
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
		return $this->greaterThan( $other ) || $this->equals( $other );
	}

	public function lessThan( RepresentsMoney $other ): bool
	{
		self::assertSameCurrency( $this, $other );

		return $this->compareTo( $other ) === -1;
	}

	public function lessThanOrEqual( RepresentsMoney $other ): bool
	{
		return $this->lessThan( $other ) || $this->equals( $other );
	}

	public function hasSameCurrency( RepresentsMoney $money ): bool
	{
		return $money->getCurrencyCode() === $this->getCurrencyCode();
	}

	public function jsonSerialize(): array
	{
		return [
			'amount'       => $this->getAmount(),
			'currencyCode' => $this->getCurrency()->getIsoCode(),
		];
	}

	private function compareTo( RepresentsMoney $other ): int
	{
		return $this->getAmount() <=> $other->getAmount();
	}

	private function guardRoundingModeIsValid( int $roundingMode ): void
	{
		if ( !isset( self::$roundingModes[ $roundingMode ] ) )
		{
			throw new InvalidRoundingModeException( sprintf( '%d is not a valid rounding mode', $roundingMode ) );
		}
	}
}
