<?php declare(strict_types=1);

namespace ComponoKit\Money\Tests\Unit;

use ComponoKit\Money\Currency;
use ComponoKit\Money\Exceptions\CurrencyMismatchException;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{

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
		$a = new Money( 1, $this->buildEurCurrency() );
		$b = new Money( 2, $this->buildEurCurrency() );
		$c = $a->add( $b );

		self::assertEquals( 1, $a->getAmount() );
		self::assertEquals( 2, $b->getAmount() );
		self::assertEquals( 3, $c->getAmount() );
	}

	public function testExceptionIsRaisedWhenMoneyObjectWithDifferentCurrencyIsAdded(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$a = new Money( 1, $this->buildEurCurrency() );
		$b = new Money( 2, $this->buildUsdCurrency() );

		$a->add( $b );
	}

	public function testAnotherMoneyObjectWithSameCurrencyCanBeSubtracted(): void
	{
		$a = new Money( 1, $this->buildEurCurrency() );
		$b = new Money( 2, $this->buildEurCurrency() );
		$c = $b->subtract( $a );

		self::assertEquals( 1, $a->getAmount() );
		self::assertEquals( 2, $b->getAmount() );
		self::assertEquals( 1, $c->getAmount() );
	}

	public function testExceptionIsRaisedWhenMoneyObjectWithDifferentCurrencyIsSubtracted(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$a = new Money( 1, $this->buildEurCurrency() );
		$b = new Money( 2, $this->buildUsdCurrency() );

		$b->subtract( $a );
	}

	public function testCanBeNegated(): void
	{
		$a = new Money( 1, $this->buildEurCurrency() );
		$b = $a->negate();

		self::assertEquals( 1, $a->getAmount() );
		self::assertEquals( -1, $b->getAmount() );
	}

	public function testCanBeMultipliedByAFactor(): void
	{
		$a = new Money( 1, $this->buildEurCurrency() );
		$b = $a->multiply( 2 );

		self::assertEquals( 1, $a->getAmount() );
		self::assertEquals( 2, $b->getAmount() );
	}

	public function testExceptionIsRaisedWhenMultipliedUsingInvalidRoundingMode(): void
	{
		$this->expectException( \DomainException::class );

		$a = new Money( 1, $this->buildEurCurrency() );
		$a->multiply( 2, 0 );
	}

	public function testCanBeDividedByAFactor(): void
	{
		$a = new Money( 6, $this->buildEurCurrency() );
		$b = $a->divide( 2 );

		self::assertEquals( 6, $a->getAmount() );
		self::assertEquals( 3, $b->getAmount() );
	}

	public function testExceptionIsRaisedWhenDividedUsingInvalidRoundingMode(): void
	{
		$this->expectException( \DomainException::class );

		$a = new Money( 6, $this->buildEurCurrency() );
		$a->divide( 2, 0 );
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

	public function testSum(): void
	{
		self::assertEquals(
			new Money( 100, $this->buildEurCurrency() ),
			Money::sum(
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 50, $this->buildEurCurrency() ),
				new Money( 40, $this->buildEurCurrency() )
			)
		);
	}

	public function testMin(): void
	{
		self::assertEquals(
			new Money( 10, $this->buildEurCurrency() ),
			Money::min(
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 50, $this->buildEurCurrency() ),
				new Money( 40, $this->buildEurCurrency() )
			)
		);
		self::assertEquals(
			new Money( 10, $this->buildEurCurrency() ),
			Money::min(
				new Money( 70, $this->buildEurCurrency() ),
				new Money( 50, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 40, $this->buildEurCurrency() )
			)
		);
	}

	public function testMax(): void
	{
		self::assertEquals(
			new Money( 50, $this->buildEurCurrency() ),
			Money::max(
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 50, $this->buildEurCurrency() ),
				new Money( 40, $this->buildEurCurrency() )
			)
		);
		self::assertEquals(
			new Money( 70, $this->buildEurCurrency() ),
			Money::max(
				new Money( 70, $this->buildEurCurrency() ),
				new Money( 50, $this->buildEurCurrency() ),
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 40, $this->buildEurCurrency() )
			)
		);
	}

	public function testAvg(): void
	{
		self::assertEquals(
			new Money( 250, $this->buildEurCurrency() ),
			Money::avg(
				new Money( 200, $this->buildEurCurrency() ),
				new Money( 200, $this->buildEurCurrency() ),
				new Money( 100, $this->buildEurCurrency() ),
				new Money( 500, $this->buildEurCurrency() )
			)
		);
	}

	private function buildEurCurrency(): RepresentsCurrency
	{
		return new Currency( 'EUR', '€', 100 );
	}

	private function buildUsdCurrency(): RepresentsCurrency
	{
		return new Currency( 'USD', '$', 100 );
	}
}
