<?php declare(strict_types=1);

namespace ComponoKit\Money;

use ComponoKit\Money\Exceptions\CurrencyMismatchException;
use ComponoKit\Money\Exceptions\InvalidRatioException;
use ComponoKit\Money\Exceptions\InvalidRoundingModeException;
use ComponoKit\Money\Helpers\RatioCalculator;
use ComponoKit\Money\Interfaces\RepresentExtractedPercentage;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;

class Money implements RepresentsMoney
{
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

	public static function fromFloat( float $amount, RepresentsCurrency $currency, int $roundingMode = PHP_ROUND_HALF_UP ): static
	{
		return new static( (int)round( $amount * $currency->getMinorUnitFactor(), 0, $roundingMode ), $currency );
	}

	public function getCurrency(): RepresentsCurrency
	{
		return $this->currency;
	}

	public static function min( RepresentsMoney $firstMoney, RepresentsMoney ...$moneyCollection ): RepresentsMoney
	{
		$min = $firstMoney;

		foreach ( $moneyCollection as $money )
		{
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
			$summedMoney = $summedMoney->add( $money );
		}

		return $summedMoney;
	}

	public function getAmount(): int
	{
		return $this->amount;
	}

	public function getCurrencyCode(): string
	{
		return $this->getCurrency()->getIsoCode();
	}

	public function add( RepresentsMoney $other ): RepresentsMoney
	{
		$this->assertSameCurrency( $other );

		return new static( $this->getAmount() + $other->getAmount(), $this->getCurrency() );
	}

	public function subtract( RepresentsMoney $other ): RepresentsMoney
	{
		$this->assertSameCurrency( $other );

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
		if ( $money->isZero() )
		{
			throw new InvalidRatioException( "Ration can't be 0" );
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
		foreach ( RatioCalculator::allocateToTargets( $this->getAmount(), $numberOfTargets ) as $share )
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
		foreach ( RatioCalculator::allocateByRatios( $this->getAmount(), $ratios ) as $share )
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
		$this->assertSameCurrency( $other );

		return $this->compareTo( $other ) === 0;
	}

	public function greaterThan( RepresentsMoney $other ): bool
	{
		$this->assertSameCurrency( $other );

		return $this->compareTo( $other ) === 1;
	}

	public function greaterThanOrEqual( RepresentsMoney $other ): bool
	{
		$this->assertSameCurrency( $other );

		return $this->greaterThan( $other ) || $this->equals( $other );
	}

	public function lessThan( RepresentsMoney $other ): bool
	{
		$this->assertSameCurrency( $other );

		return $this->compareTo( $other ) === -1;
	}

	public function lessThanOrEqual( RepresentsMoney $other ): bool
	{
		$this->assertSameCurrency( $other );

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

	private function assertSameCurrency( RepresentsMoney $money ): void
	{
		if ( !$this->hasSameCurrency( $money ) )
		{
			throw new CurrencyMismatchException( sprintf( 'Currency mismatch: %s != %s', $this->getCurrencyCode(), $money->getCurrencyCode() ) );
		}
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
