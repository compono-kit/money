<?php declare(strict_types=1);

namespace ComponoKit\Money\Tests\Unit;

use ComponoKit\Money\Exceptions\CurrencyMismatchException;
use ComponoKit\Money\Exceptions\InvalidRoundingModeException;
use ComponoKit\Money\Money;
use ComponoKit\Money\Tests\Unit\Traits\BuildingCurrencies;
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
		self::assertEquals( 1234, (new Money( 1234, $this->buildEurCurrency() ))->getAmount() );
	}

	public function testGetCurrencyCode(): void
	{
		self::assertEquals( 'EUR', (new Money( 0, $this->buildEurCurrency() ))->getCurrencyCode() );
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

	public function testExceptionIsRaisedWhenMultipliedUsingInvalidRoundingMode(): void
	{
		$this->expectException( InvalidRoundingModeException::class );

		$money = new Money( 1, $this->buildEurCurrency() );
		$money->multiply( 2, 0 );
	}

	public function testCanBeDividedByAFactor(): void
	{
		$money1 = new Money( 6, $this->buildEurCurrency() );
		$money2 = $money1->divide( 2 );

		self::assertEquals( 6, $money1->getAmount() );
		self::assertEquals( 3, $money2->getAmount() );
	}

	public function testExceptionIsRaisedWhenDividedUsingInvalidRoundingMode(): void
	{
		$this->expectException( InvalidRoundingModeException::class );

		$money = new Money( 6, $this->buildEurCurrency() );
		$money->divide( 2, 0 );
	}

	public function testModulo(): void
	{
		self::assertEquals( new Money( 2, $this->buildEurCurrency() ), (new Money( 5, $this->buildEurCurrency() ))->mod( new Money( 3, $this->buildEurCurrency() ) ) );
	}

	public function testAbsolute(): void
	{
		self::assertEquals( new Money( 2, $this->buildEurCurrency() ), (new Money( -2, $this->buildEurCurrency() ))->absolute() );
	}

	public function testNegate(): void
	{
		self::assertEquals( new Money( -2, $this->buildEurCurrency() ), (new Money( 2, $this->buildEurCurrency() ))->negate() );
	}

	public function testRatioOf(): void
	{
		$money = new Money( 10, $this->buildEurCurrency() );

		self::assertEquals( 2, $money->ratioOf( new Money( 5, $this->buildEurCurrency() ) ) );
		self::assertEquals( 0.5, $money->ratioOf( new Money( 20, $this->buildEurCurrency() ) ) );
	}

	public function testIsZero(): void
	{
		self::assertFalse( (new Money( 1, $this->buildEurCurrency() ))->isZero() );
		self::assertFalse( (new Money( -1, $this->buildEurCurrency() ))->isZero() );
		self::assertTrue( (new Money( 0, $this->buildEurCurrency() ))->isZero() );
	}

	public function testIsPositive(): void
	{
		self::assertFalse( (new Money( -1, $this->buildEurCurrency() ))->isPositive() );
		self::assertFalse( (new Money( 0, $this->buildEurCurrency() ))->isPositive() );
		self::assertTrue( (new Money( 1, $this->buildEurCurrency() ))->isPositive() );
	}

	public function testIsNegative(): void
	{
		self::assertFalse( (new Money( 0, $this->buildEurCurrency() ))->isNegative() );
		self::assertFalse( (new Money( 1, $this->buildEurCurrency() ))->isNegative() );
		self::assertTrue( (new Money( -1, $this->buildEurCurrency() ))->isNegative() );
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
		self::assertTrue( (new Money( 1, $this->buildEurCurrency() ))->hasSameCurrency( new Money( 5, $this->buildEurCurrency() ) ) );
		self::assertFalse( (new Money( 1, $this->buildEurCurrency() ))->hasSameCurrency( new Money( 1, $this->buildUsdCurrency() ) ) );
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
			'{"amount":1,"currencyCode":"EUR"}',
			json_encode( new Money( 1, $this->buildEurCurrency() ), JSON_THROW_ON_ERROR )
		);
	}
}
