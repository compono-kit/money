<?php declare(strict_types=1);

namespace Componium\Money;

use Componium\Money\Exceptions\CurrencyMismatchException;
use Componium\Money\Interfaces\RepresentExtractedPercentage;
use Componium\Money\Interfaces\RepresentsMoney;
use Money\Calculator\BcMathCalculator;
use Money\Currency;
use Money\Money as BaseMoney;

class Money implements RepresentsMoney, \JsonSerializable
{
	private BaseMoney $money;

	/** @var int[] */
	private static array $roundingModes = [
		PHP_ROUND_HALF_UP   => BaseMoney::ROUND_HALF_UP,
		PHP_ROUND_HALF_DOWN => BaseMoney::ROUND_HALF_DOWN,
		PHP_ROUND_HALF_EVEN => BaseMoney::ROUND_HALF_EVEN,
		PHP_ROUND_HALF_ODD  => BaseMoney::ROUND_HALF_ODD,
	];

	public function __construct( int|string $amount, string $currencyCode )
	{
		$this->money = new BaseMoney( $amount, new Currency( strtoupper( $currencyCode ) ) );
	}

	/**
	 * @param RepresentsMoney $money
	 *
	 * @return static
	 */
	public static function newByMoney( RepresentsMoney $money ): RepresentsMoney
	{
		return new static( $money->getAmount(), $money->getCurrencyCode() );
	}

	public static function newByFloat( float $amount, string $currencyCode, int $subUnit = 100, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentsMoney
	{
		return new static( BcMathCalculator::round( BcMathCalculator::multiply( (string)$amount, (string)$subUnit ), $roundingMode ), $currencyCode );
	}

	public static function newByBaseMoney( BaseMoney $money ): Money
	{
		return new static( (int)$money->getAmount(), $money->getCurrency()->getCode() );
	}

	public static function min( RepresentsMoney $money, RepresentsMoney ...$collection ): Money
	{
		return self::newByBaseMoney( BaseMoney::min( self::toBaseMoney( $money ), ...self::toBaseMoneyCollection( ...$collection ) ) );
	}

	public static function max( RepresentsMoney $money, RepresentsMoney ...$collection ): Money
	{
		return self::newByBaseMoney( BaseMoney::max( self::toBaseMoney( $money ), ...self::toBaseMoneyCollection( ...$collection ) ) );
	}

	public static function avg( RepresentsMoney $money, RepresentsMoney ...$collection ): Money
	{
		return self::newByBaseMoney( BaseMoney::avg( self::toBaseMoney( $money ), ...self::toBaseMoneyCollection( ...$collection ) ) );
	}

	public static function sum( RepresentsMoney $money, RepresentsMoney ...$collection ): Money
	{
		return self::newByBaseMoney( BaseMoney::sum( self::toBaseMoney( $money ), ...self::toBaseMoneyCollection( ...$collection ) ) );
	}

	public function jsonSerialize(): array
	{
		return [
			'amount'       => $this->money->getAmount(),
			'currencyCode' => $this->money->getCurrency()->getCode(),
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

		return self::newByBaseMoney( $this->money->add( new BaseMoney( $other->getAmount(), $this->money->getCurrency() ) ) );
	}

	public function subtract( RepresentsMoney $other ): RepresentsMoney
	{
		$this->assertSameCurrency( $other );

		return self::newByBaseMoney( $this->money->subtract( new BaseMoney( $other->getAmount(), $this->money->getCurrency() ) ) );
	}

	public function multiply( float $factor, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentsMoney
	{
		$this->guardRoundingModeIsValid( $roundingMode );

		return self::newByBaseMoney( $this->money->multiply( sprintf( '%.14F', $factor ), self::$roundingModes[ $roundingMode ] ) );
	}

	public function divide( float $factor, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentsMoney
	{
		$this->guardRoundingModeIsValid( $roundingMode );

		return self::newByBaseMoney( $this->money->divide( sprintf( '%.14F', $factor ), self::$roundingModes[ $roundingMode ] ) );
	}

	public function mod( RepresentsMoney $money ): RepresentsMoney
	{
		return self::newByBaseMoney( $this->money->mod( self::toBaseMoney( $money ) ) );
	}

	public function absolute(): RepresentsMoney
	{
		return self::newByBaseMoney( $this->money->absolute() );
	}

	public function negate(): RepresentsMoney
	{
		return new self( -1 * (int)$this->money->getAmount(), $this->getCurrencyCode() );
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
	 * @param int $targetCount
	 *
	 * @return RepresentsMoney[]
	 */
	public function allocateToTargets( int $targetCount ): array
	{
		$allocatedMoney = [];
		foreach ( $this->money->allocateTo( $targetCount ) as $money )
		{
			$allocatedMoney[] = self::newByBaseMoney( $money );
		}

		return $allocatedMoney;
	}

	/**
	 * @param array|int[] $ratios
	 *
	 * @return RepresentsMoney[]
	 */
	public function allocateByRatios( array $ratios ): array
	{
		$allocatedMoney = [];
		foreach ( $this->money->allocate( $ratios ) as $money )
		{
			$allocatedMoney[] = self::newByBaseMoney( $money );
		}

		return $allocatedMoney;
	}

	public function extractPercentage( float $percentage, int $roundingMode = PHP_ROUND_HALF_UP ): RepresentExtractedPercentage
	{
		$this->guardRoundingModeIsValid( $roundingMode );

		$percentageAmount = new self(
			(int)BcMathCalculator::round(
				sprintf( '%.14F', $this->money->getAmount() / (100 + $percentage) * $percentage ),
				self::$roundingModes[ $roundingMode ]
			),
			$this->money->getCurrency()->getCode()
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
		return new BaseMoney( $money->getAmount(), new Currency( $money->getCurrencyCode() ) );
	}

	private static function toBaseMoneyCollection( RepresentsMoney ...$collection ): array
	{
		$baseMoneyCollection = [];
		foreach ( $collection as $money )
		{
			$baseMoneyCollection[] = self::toBaseMoney( $money );
		}

		return $baseMoneyCollection;
	}

	private function guardRoundingModeIsValid( int $roundingMode ): void
	{
		if ( !isset( self::$roundingModes[ $roundingMode ] ) )
		{
			throw new \DomainException( sprintf( '%d is not a valid rounding mode', $roundingMode ) );
		}
	}
}
