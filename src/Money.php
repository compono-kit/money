<?php declare(strict_types=1);

namespace ComponoKit\Money;

use ComponoKit\Money\Exceptions\CurrencyMismatchException;
use ComponoKit\Money\Formatters\DecimalMoneyFormatter;
use ComponoKit\Money\Interfaces\FormatsMoneyString;
use ComponoKit\Money\Interfaces\RepresentExtractedPercentage;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use Money\Calculator\BcMathCalculator;
use Money\Currency as BaseCurrency;
use Money\Money as BaseMoney;

class Money implements RepresentsMoney
{
	private BaseMoney $money;

	/** @var int[] */
	private static array $roundingModes = [
		PHP_ROUND_HALF_UP   => BaseMoney::ROUND_HALF_UP,
		PHP_ROUND_HALF_DOWN => BaseMoney::ROUND_HALF_DOWN,
		PHP_ROUND_HALF_EVEN => BaseMoney::ROUND_HALF_EVEN,
		PHP_ROUND_HALF_ODD  => BaseMoney::ROUND_HALF_ODD,
	];

	public function __construct( int|string $amount, private readonly RepresentsCurrency $currency, private readonly FormatsMoneyString $moneyFormatter = new DecimalMoneyFormatter() )
	{
		$this->money = new BaseMoney( $amount, new BaseCurrency( $currency->getIsoCode() ) );
	}

	public static function fromFloat( float $amount, RepresentsCurrency $currency, int $roundingMode = PHP_ROUND_HALF_UP ): static
	{
		return new static( BcMathCalculator::round( BcMathCalculator::multiply( (string)$amount, (string)$currency->getMinorUnitFactor() ), $roundingMode ), $currency );
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
		return $firstMoney->add( ...$moneyCollection );
	}

	public function jsonSerialize(): array
	{
		return [
			'amount'       => $this->money->getAmount(),
			'currencyCode' => $this->getCurrency()->getIsoCode(),
		];
	}

	public function getAmount(): int
	{
		return (int)$this->money->getAmount();
	}

	public function getCurrencyCode(): string
	{
		return $this->money->getCurrency()->getCode();
	}

	public function add( RepresentsMoney $other ): RepresentsMoney
	{
		$this->assertSameCurrency( $other );

		return new static( $this->money->add( self::toBaseMoney( $other ) )->getAmount(), $this->getCurrency() );
	}

	public function subtract( RepresentsMoney $other ): RepresentsMoney
	{
		$this->assertSameCurrency( $other );

		return new static( $this->money->subtract( self::toBaseMoney( $other ) )->getAmount(), $this->getCurrency() );
	}

	public function multiply( float $factor, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentsMoney
	{
		$this->guardRoundingModeIsValid( $roundingMode );

		return new static( $this->money->multiply( sprintf( '%.14F', $factor ), self::$roundingModes[ $roundingMode ] )->getAmount(), $this->getCurrency() );
	}

	public function divide( float $factor, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentsMoney
	{
		$this->guardRoundingModeIsValid( $roundingMode );

		return new static( $this->money->divide( sprintf( '%.14F', $factor ), self::$roundingModes[ $roundingMode ] )->getAmount(), $this->getCurrency() );
	}

	public function mod( RepresentsMoney $money ): RepresentsMoney
	{
		return new static( $this->money->mod( self::toBaseMoney( $money ) )->getAmount(), $this->getCurrency() );
	}

	public function absolute(): RepresentsMoney
	{
		return new static( $this->money->absolute()->getAmount(), $this->getCurrency() );
	}

	public function negate(): RepresentsMoney
	{
		return new static( -1 * (int)$this->money->getAmount(), $this->getCurrency() );
	}

	public function ratioOf( RepresentsMoney $money ): float
	{
		return (float)$this->money->ratioOf( self::toBaseMoney( $money ) );
	}

	public function isZero(): bool
	{
		return $this->money->isZero();
	}

	public function isPositive(): bool
	{
		return $this->money->isPositive();
	}

	public function isNegative(): bool
	{
		return $this->money->isNegative();
	}

	/**
	 * @param int $numberOfTargets
	 *
	 * @return \Iterator<int,RepresentsMoney>
	 */
	public function allocateToTargets( int $numberOfTargets ): \Iterator
	{
		foreach ( $this->money->allocateTo( $numberOfTargets ) as $money )
		{
			yield new static( $money->getAmount(), $this->getCurrency() );
		}
	}

	/**
	 * @param array|int[] $ratios
	 *
	 * @return \Iterator<int,RepresentsMoney>
	 */
	public function allocateByRatios( array $ratios ): \Iterator
	{
		foreach ( $this->money->allocate( $ratios ) as $money )
		{
			yield new static( $money->getAmount(), $this->getCurrency() );
		}
	}

	public function extractPercentage( float $percentage, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentExtractedPercentage
	{
		$this->guardRoundingModeIsValid( $roundingMode );

		$percentageAmount = new self(
			(int)BcMathCalculator::round(
				sprintf( '%.14F', $this->money->getAmount() / (100 + $percentage) * $percentage ),
				self::$roundingModes[ $roundingMode ]
			),
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

	public function __toString(): string
	{
		return $this->moneyFormatter->formatString( $this );
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

	private static function toBaseMoney( RepresentsMoney $money ): BaseMoney
	{
		return new BaseMoney( $money->getAmount(), new BaseCurrency( $money->getCurrencyCode() ) );
	}

	private function guardRoundingModeIsValid( int $roundingMode ): void
	{
		if ( !isset( self::$roundingModes[ $roundingMode ] ) )
		{
			throw new \DomainException( sprintf( '%d is not a valid rounding mode', $roundingMode ) );
		}
	}
}
