<?php declare(strict_types=1);

namespace ComponoKit\Money\Tests\Unit;

use ComponoKit\Money\Exceptions\AmountOutOfRangeException;
use ComponoKit\Money\Exceptions\CurrencyMismatchException;
use ComponoKit\Money\Exceptions\InvalidRatioException;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Money;
use ComponoKit\Money\Tests\Unit\Traits\BuildingCurrencies;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
	use BuildingCurrencies;

	public function testObjectCanBeConstructed(): void
	{
		self::assertInstanceOf( Money::class, new Money( 0, $this->buildEurCurrency() ) );
	}

	public function testGetAmount(): void
	{
		self::assertEquals( 1234, new Money( 1234, $this->buildEurCurrency() )->getAmount() );
	}

	public function testGetCurrencyCode(): void
	{
		self::assertEquals( 'EUR', new Money( 0, $this->buildEurCurrency() )->getCurrencyCode() );
	}

	public function testAnotherMoneyObjectWithSameCurrencyCanBeAdded(): void
	{
		$money1 = new Money( 1, $this->buildEurCurrency() );
		$money2 = new Money( 2, $this->buildEurCurrency() );
		$money3 = $money1->add( $money2 );

		self::assertEquals( 1, $money1->getAmount() );
		self::assertEquals( 2, $money2->getAmount() );
		self::assertEquals( 3, $money3->getAmount() );
	}

	public function testExceptionIsRaisedWhenMoneyObjectWithDifferentCurrencyIsAdded(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$money1 = new Money( 1, $this->buildEurCurrency() );
		$money2 = new Money( 2, $this->buildUsdCurrency() );

		$money1->add( $money2 );
	}

	public function testAnotherMoneyObjectWithSameCurrencyCanBeSubtracted(): void
	{
		$money1 = new Money( 1, $this->buildEurCurrency() );
		$money2 = new Money( 2, $this->buildEurCurrency() );
		$money3 = $money2->subtract( $money1 );

		self::assertEquals( 1, $money1->getAmount() );
		self::assertEquals( 2, $money2->getAmount() );
		self::assertEquals( 1, $money3->getAmount() );
	}

	public function testExceptionIsRaisedWhenMoneyObjectWithDifferentCurrencyIsSubtracted(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$money1 = new Money( 1, $this->buildEurCurrency() );
		$money2 = new Money( 2, $this->buildUsdCurrency() );

		$money2->subtract( $money1 );
	}

	public function testCanBeNegated(): void
	{
		$money1 = new Money( 1, $this->buildEurCurrency() );
		$money2 = $money1->negate();

		self::assertEquals( 1, $money1->getAmount() );
		self::assertEquals( -1, $money2->getAmount() );
	}

	public function testCanBeMultipliedByAFactor(): void
	{
		$money1 = new Money( 1, $this->buildEurCurrency() );
		$money2 = $money1->multiply( 2 );

		self::assertEquals( 1, $money1->getAmount() );
		self::assertEquals( 2, $money2->getAmount() );
	}

	public static function RoundingModeDataProvider(): \Iterator
	{
		yield 'half away from zero positive' => [ 5, \RoundingMode::HalfAwayFromZero, 3 ];
		yield 'half away from zero negative' => [ -5, \RoundingMode::HalfAwayFromZero, -3 ];
		yield 'half towards zero positive' => [ 5, \RoundingMode::HalfTowardsZero, 2 ];
		yield 'half towards zero negative' => [ -5, \RoundingMode::HalfTowardsZero, -2 ];
		yield 'half even' => [ 5, \RoundingMode::HalfEven, 2 ];
		yield 'half odd' => [ 5, \RoundingMode::HalfOdd, 3 ];
		yield 'towards zero' => [ 7, \RoundingMode::TowardsZero, 3 ];
		yield 'away from zero' => [ 7, \RoundingMode::AwayFromZero, 4 ];
		yield 'negative infinity' => [ -7, \RoundingMode::NegativeInfinity, -4 ];
		yield 'positive infinity' => [ -7, \RoundingMode::PositiveInfinity, -3 ];
	}

	#[DataProvider( 'RoundingModeDataProvider' )]
	public function testMultiplyUsesRoundingMode( int $amount, \RoundingMode $roundingMode, int $expectedAmount ): void
	{
		self::assertSame( $expectedAmount, new Money( $amount, $this->buildEurCurrency() )->multiply( 0.5, $roundingMode )->getAmount() );
	}

	#[DataProvider( 'RoundingModeDataProvider' )]
	public function testDivideUsesRoundingMode( int $amount, \RoundingMode $roundingMode, int $expectedAmount ): void
	{
		self::assertSame( $expectedAmount, new Money( $amount, $this->buildEurCurrency() )->divide( 2, $roundingMode )->getAmount() );
	}

	#[DataProvider( 'RoundingModeDataProvider' )]
	public function testFromFloatUsesRoundingMode( int $amount, \RoundingMode $roundingMode, int $expectedAmount ): void
	{
		self::assertSame( $expectedAmount, Money::fromFloat( $amount / 200, $this->buildEurCurrency(), $roundingMode )->getAmount() );
	}

	public static function AmountOutOfRangeDataProvider(): \Iterator
	{
		yield 'multiply' => [ static fn( Money $money ) => $money->multiply( 1e30 ) ];
		yield 'divide' => [ static fn( Money $money ) => $money->divide( 1e-30 ) ];
		yield 'multiply by infinity' => [ static fn( Money $money ) => $money->multiply( INF ) ];
		yield 'add' => [ static fn( Money $money ) => new Money( PHP_INT_MAX, $money->getCurrency() )->add( $money ) ];
		yield 'subtract' => [ static fn( Money $money ) => new Money( PHP_INT_MIN, $money->getCurrency() )->subtract( $money ) ];
		yield 'negate' => [ static fn( Money $money ) => new Money( PHP_INT_MIN, $money->getCurrency() )->negate() ];
		yield 'absolute' => [ static fn( Money $money ) => new Money( PHP_INT_MIN, $money->getCurrency() )->absolute() ];
		yield 'from float' => [ static fn( Money $money ) => Money::fromFloat( 1e30, $money->getCurrency() ) ];
	}

	#[DataProvider( 'AmountOutOfRangeDataProvider' )]
	public function testAmountOutOfRangeThrowsException( \Closure $calculation ): void
	{
		$this->expectException( AmountOutOfRangeException::class );

		$calculation( new Money( 1, $this->buildEurCurrency() ) );
	}

	public function testHasSameCurrencyWithForeignCurrencyImplementation(): void
	{
		$foreignCurrency = $this->createStub( RepresentsCurrency::class );
		$foreignCurrency->method( 'getIsoCode' )->willReturn( 'EUR' );
		$foreignCurrency->method( 'equals' )->willReturnCallback( static fn( RepresentsCurrency $other ): bool => $other->getIsoCode() === 'EUR' );

		self::assertTrue( new Money( 1, $this->buildEurCurrency() )->hasSameCurrency( new Money( 2, $foreignCurrency ) ) );
		self::assertTrue( new Money( 1, $this->buildEurCurrency() )->add( new Money( 2, $foreignCurrency ) )->equals( new Money( 3, $this->buildEurCurrency() ) ) );
	}

	public function testAllocateToNegativeNumberOfTargetsThrowsException(): void
	{
		$this->expectException( InvalidRatioException::class );

		iterator_to_array( new Money( 1, $this->buildEurCurrency() )->allocateToTargets( -1 ) );
	}

	public function testAllocateByNonIntegerRatiosThrowsException(): void
	{
		$this->expectException( InvalidRatioException::class );

		iterator_to_array( new Money( 1, $this->buildEurCurrency() )->allocateByRatios( [ 1.5, 2 ] ) );
	}

	public function testCanBeDividedByAFactor(): void
	{
		$money1 = new Money( 6, $this->buildEurCurrency() );
		$money2 = $money1->divide( 2 );

		self::assertEquals( 6, $money1->getAmount() );
		self::assertEquals( 3, $money2->getAmount() );
	}

	public function testModulo(): void
	{
		self::assertEquals( new Money( 2, $this->buildEurCurrency() ), new Money( 5, $this->buildEurCurrency() )->mod( new Money( 3, $this->buildEurCurrency() ) ) );
	}

	public function testAbsolute(): void
	{
		self::assertEquals( new Money( 2, $this->buildEurCurrency() ), new Money( -2, $this->buildEurCurrency() )->absolute() );
	}

	public function testNegate(): void
	{
		self::assertEquals( new Money( -2, $this->buildEurCurrency() ), new Money( 2, $this->buildEurCurrency() )->negate() );
	}

	public function testRatioOf(): void
	{
		$money = new Money( 10, $this->buildEurCurrency() );

		self::assertEquals( 2, $money->ratioOf( new Money( 5, $this->buildEurCurrency() ) ) );
		self::assertEquals( 0.5, $money->ratioOf( new Money( 20, $this->buildEurCurrency() ) ) );
	}

	public function testIsZero(): void
	{
		self::assertFalse( new Money( 1, $this->buildEurCurrency() )->isZero() );
		self::assertFalse( new Money( -1, $this->buildEurCurrency() )->isZero() );
		self::assertTrue( new Money( 0, $this->buildEurCurrency() )->isZero() );
	}

	public function testIsPositive(): void
	{
		self::assertFalse( new Money( -1, $this->buildEurCurrency() )->isPositive() );
		self::assertFalse( new Money( 0, $this->buildEurCurrency() )->isPositive() );
		self::assertTrue( new Money( 1, $this->buildEurCurrency() )->isPositive() );
	}

	public function testIsNegative(): void
	{
		self::assertFalse( new Money( 0, $this->buildEurCurrency() )->isNegative() );
		self::assertFalse( new Money( 1, $this->buildEurCurrency() )->isNegative() );
		self::assertTrue( new Money( -1, $this->buildEurCurrency() )->isNegative() );
	}

	public function testCanBeAllocatedToNumberOfTargets(): void
	{
		$money     = new Money( 99, $this->buildEurCurrency() );
		$allocated = iterator_to_array( $money->allocateToTargets( 10 ) );

		self::assertEquals(
			[
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 9, $this->buildEurCurrency() ),
			],
			$allocated
		);
	}

	public function testPercentageCanBeExtracted(): void
	{
		$original = new Money( 10000, $this->buildEurCurrency() );
		$extract  = $original->extractPercentage( 21 );

		self::assertEquals( new Money( 1736, $this->buildEurCurrency() ), $extract->getPercentage() );
		self::assertEquals( new Money( 8264, $this->buildEurCurrency() ), $extract->getSubTotal() );
	}

	public function testCanBeAllocatedByRatios(): void
	{
		$money     = new Money( 5, $this->buildEurCurrency() );
		$allocated = iterator_to_array( $money->allocateByRatios( [ 3, 7 ] ) );

		self::assertEquals(
			[
				new Money( 2, $this->buildEurCurrency() ),
				new Money( 3, $this->buildEurCurrency() ),
			],
			$allocated
		);
	}

	public function testGreaterThan(): void
	{
		$lessMoney = new Money( 1, $this->buildEurCurrency() );
		$moreMoney = new Money( 2, $this->buildEurCurrency() );

		self::assertFalse( $lessMoney->greaterThan( $moreMoney ) );
		self::assertTrue( $moreMoney->greaterThan( $lessMoney ) );
	}

	public function testLessThan(): void
	{
		$lessMoney = new Money( 1, $this->buildEurCurrency() );
		$moreMoney = new Money( 2, $this->buildEurCurrency() );

		self::assertTrue( $lessMoney->lessThan( $moreMoney ) );
		self::assertFalse( $lessMoney->greaterThan( $moreMoney ) );
	}

	public function testEquals(): void
	{
		$money     = new Money( 1, $this->buildEurCurrency() );
		$sameMoney = new Money( 1, $this->buildEurCurrency() );

		self::assertTrue( $money->equals( $sameMoney ) );
		self::assertTrue( $sameMoney->equals( $money ) );
	}

	public function testGreaterThanOrEqual(): void
	{
		$money1 = new Money( 2, $this->buildEurCurrency() );
		$money2 = new Money( 2, $this->buildEurCurrency() );
		$money3 = new Money( 1, $this->buildEurCurrency() );

		self::assertTrue( $money1->greaterThanOrEqual( $money1 ) );
		self::assertTrue( $money1->greaterThanOrEqual( $money2 ) );
		self::assertTrue( $money1->greaterThanOrEqual( $money3 ) );
		self::assertFalse( $money3->greaterThanOrEqual( $money1 ) );
	}

	public function testLessThanOrEqual(): void
	{
		$money1 = new Money( 1, $this->buildEurCurrency() );
		$money2 = new Money( 1, $this->buildEurCurrency() );
		$money3 = new Money( 2, $this->buildEurCurrency() );

		self::assertTrue( $money1->lessThanOrEqual( $money1 ) );
		self::assertTrue( $money1->lessThanOrEqual( $money2 ) );
		self::assertTrue( $money1->lessThanOrEqual( $money3 ) );
		self::assertFalse( $money3->lessThanOrEqual( $money1 ) );
	}

	public function testHasSameCurrency(): void
	{
		self::assertTrue( new Money( 1, $this->buildEurCurrency() )->hasSameCurrency( new Money( 5, $this->buildEurCurrency() ) ) );
		self::assertFalse( new Money( 1, $this->buildEurCurrency() )->hasSameCurrency( new Money( 1, $this->buildUsdCurrency() ) ) );
	}

	public function testIfCurrencyMismatchExceptionIsThrownIfCurrenciesDoesNotMatchUsingEquals(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$eurMoney = new Money( 1, $this->buildEurCurrency() );
		$usdMoney = new Money( 2, $this->buildUsdCurrency() );

		$eurMoney->equals( $usdMoney );
	}

	public function testIfCurrencyMismatchExceptionIsThrownIfCurrenciesDoesNotMatchUsingGreaterThan(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$eurMoney = new Money( 1, $this->buildEurCurrency() );
		$usdMoney = new Money( 2, $this->buildUsdCurrency() );

		$eurMoney->greaterThan( $usdMoney );
	}

	public function testIfCurrencyMismatchExceptionIsThrownIfCurrenciesDoesNotMatchUsingLessThan(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$eurMoney = new Money( 1, $this->buildEurCurrency() );
		$usdMoney = new Money( 2, $this->buildUsdCurrency() );

		$eurMoney->lessThan( $usdMoney );
	}

	public function testIfCurrencyMismatchExceptionIsThrownIfCurrenciesDoesNotMatchUsingGreaterThanOrEqual(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$eurMoney = new Money( 1, $this->buildEurCurrency() );
		$usdMoney = new Money( 2, $this->buildUsdCurrency() );

		$eurMoney->greaterThanOrEqual( $usdMoney );
	}

	public function testIfCurrencyMismatchExceptionIsThrownIfCurrenciesDoesNotMatchUsingLessThanOrEqual(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$eurMoney = new Money( 1, $this->buildEurCurrency() );
		$usdMoney = new Money( 2, $this->buildUsdCurrency() );

		$eurMoney->lessThanOrEqual( $usdMoney );
	}

	public function testCanBeSerializedToJson(): void
	{
		self::assertEquals(
			'{"amount":1,"currency":{"iso-code":"EUR","symbol":"\u20ac","minor-unit-factor":100,"minor-unit":2}}',
			json_encode( new Money( 1, $this->buildEurCurrency() ), JSON_THROW_ON_ERROR )
		);
	}
}
