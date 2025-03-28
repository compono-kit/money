<?php declare(strict_types=1);

namespace Componium\Money\Tests\Unit;

use Componium\Money\Exceptions\CurrencyMismatchException;
use Componium\Money\Money;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
	public function testObjectCanBeConstructedForValidConstructorArguments()
	{
		$m = new Money( 0, 'EUR' );

		self::assertInstanceOf( 'Componium\\Money\\Money', $m );

		return $m;
	}

	public function testObjectCanBeConstructedForValidConstructorArguments2()
	{
		$m = new Money( 0, 'EUR' );

		self::assertInstanceOf( 'Componium\\Money\\Money', $m );

		return $m;
	}

	public function testObjectCanBeConstructedFromAnotherMoneyObject(): void
	{
		self::assertEquals(
			new Money( 1234, 'EUR' ),
			Money::newByMoney( new Money( 1234, 'EUR' ) )
		);
	}

	#[Depends('testObjectCanBeConstructedForValidConstructorArguments')]
	public function testAmountCanBeRetrieved( Money $m ): void
	{
		self::assertEquals( 0, $m->getAmount() );
	}

	#[Depends('testObjectCanBeConstructedForValidConstructorArguments')]
	public function testCurrencyCanBeRetrieved( Money $m ): void
	{
		self::assertEquals( 'EUR', $m->getCurrencyCode() );
	}

	public function testAnotherMoneyObjectWithSameCurrencyCanBeAdded(): void
	{
		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'EUR' );
		$c = $a->add( $b );

		self::assertEquals( 1, $a->getAmount() );
		self::assertEquals( 2, $b->getAmount() );
		self::assertEquals( 3, $c->getAmount() );
	}

	public function testExceptionIsRaisedWhenMoneyObjectWithDifferentCurrencyIsAdded(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'USD' );

		$a->add( $b );
	}

	public function testAnotherMoneyObjectWithSameCurrencyCanBeSubtracted(): void
	{
		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'EUR' );
		$c = $b->subtract( $a );

		self::assertEquals( 1, $a->getAmount() );
		self::assertEquals( 2, $b->getAmount() );
		self::assertEquals( 1, $c->getAmount() );
	}

	public function testExceptionIsRaisedWhenMoneyObjectWithDifferentCurrencyIsSubtracted(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'USD' );

		$b->subtract( $a );
	}

	public function testCanBeNegated(): void
	{
		$a = new Money( 1, 'EUR' );
		$b = $a->negate();

		self::assertEquals( 1, $a->getAmount() );
		self::assertEquals( -1, $b->getAmount() );
	}

	public function testCanBeMultipliedByAFactor(): void
	{
		$a = new Money( 1, 'EUR' );
		$b = $a->multiply( 2 );

		self::assertEquals( 1, $a->getAmount() );
		self::assertEquals( 2, $b->getAmount() );
	}

	public function testExceptionIsRaisedWhenMultipliedUsingInvalidRoundingMode(): void
	{
		$this->expectException( \DomainException::class );

		$a = new Money( 1, 'EUR' );
		$a->multiply( 2, 0 );
	}

	public function testCanBeDividedByAFactor(): void
	{
		$a = new Money( 6, 'EUR' );
		$b = $a->divide( 2 );

		self::assertEquals( 6, $a->getAmount() );
		self::assertEquals( 3, $b->getAmount() );
	}

	public function testExceptionIsRaisedWhenDividedUsingInvalidRoundingMode(): void
	{
		$this->expectException( \DomainException::class );

		$a = new Money( 6, 'EUR' );
		$a->divide( 2, 0 );
	}

	public function testModulo(): void
	{
		self::assertEquals( new Money( 2, 'EUR' ), (new Money( 5, 'EUR' ))->mod( (new Money( 3, 'EUR' )) ) );
	}

	public function testAbsolute(): void
	{
		self::assertEquals( new Money( 2, 'EUR' ), (new Money( -2, 'EUR' ))->absolute() );
	}

	public function testNegate(): void
	{
		self::assertEquals( new Money( -2, 'EUR' ), (new Money( 2, 'EUR' ))->negate() );
	}

	public function testRatioOf(): void
	{
		$money = new Money( 10, 'EUR' );

		self::assertEquals( 2, $money->ratioOf( new Money( 5, 'EUR' ) ) );
		self::assertEquals( 0.5, $money->ratioOf( new Money( 20, 'EUR' ) ) );
	}

	public function testIsZero(): void
	{
		self::assertFalse( (new Money( 1, 'EUR' ))->isZero() );
		self::assertFalse( (new Money( -1, 'EUR' ))->isZero() );
		self::assertTrue( (new Money( 0, 'EUR' ))->isZero() );
	}

	public function testIsPositive(): void
	{
		self::assertFalse( (new Money( -1, 'EUR' ))->isPositive() );
		self::assertFalse( (new Money( 0, 'EUR' ))->isPositive() );
		self::assertTrue( (new Money( 1, 'EUR' ))->isPositive() );
	}

	public function testIsNegative(): void
	{
		self::assertFalse( (new Money( 0, 'EUR' ))->isNegative() );
		self::assertFalse( (new Money( 1, 'EUR' ))->isNegative() );
		self::assertTrue( (new Money( -1, 'EUR' ))->isNegative() );
	}

	public function testCanBeAllocatedToNumberOfTargets(): void
	{
		$a = new Money( 99, 'EUR' );
		$r = $a->allocateToTargets( 10 );

		self::assertEquals(
			[
				new Money( 10, 'EUR' ),
				new Money( 10, 'EUR' ),
				new Money( 10, 'EUR' ),
				new Money( 10, 'EUR' ),
				new Money( 10, 'EUR' ),
				new Money( 10, 'EUR' ),
				new Money( 10, 'EUR' ),
				new Money( 10, 'EUR' ),
				new Money( 10, 'EUR' ),
				new Money( 9, 'EUR' ),
			],
			$r
		);
	}

	public function testPercentageCanBeExtracted(): void
	{
		$original = new Money( 10000, 'EUR' );
		$extract  = $original->extractPercentage( 21 );

		self::assertEquals( new Money( 8264, 'EUR' ), $extract->getSubTotal() );
		self::assertEquals( new Money( 1736, 'EUR' ), $extract->getPercentage() );
	}

	public function testCanBeAllocatedByRatios(): void
	{
		$a = new Money( 5, 'EUR' );
		$r = $a->allocateByRatios( [ 3, 7 ] );

		self::assertEquals(
			[
				new Money( 2, 'EUR' ),
				new Money( 3, 'EUR' ),
			],
			$r
		);
	}

	public function testCanBeComparedToAnotherMoneyObjectWithSameCurrency2(): void
	{
		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'EUR' );

		self::assertFalse( $a->greaterThan( $b ) );
		self::assertTrue( $b->greaterThan( $a ) );
	}

	public function testCanBeComparedToAnotherMoneyObjectWithSameCurrency3(): void
	{
		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'EUR' );

		self::assertFalse( $b->lessThan( $a ) );
		self::assertTrue( $a->lessThan( $b ) );
	}

	public function testCanBeComparedToAnotherMoneyObjectWithSameCurrency4(): void
	{
		$a = new Money( 1, 'EUR' );
		$b = new Money( 1, 'EUR' );

		self::assertTrue( $a->equals( $b ) );
		self::assertTrue( $b->equals( $a ) );
	}

	public function testCanBeComparedToAnotherMoneyObjectWithSameCurrency5(): void
	{
		$a = new Money( 2, 'EUR' );
		$b = new Money( 2, 'EUR' );
		$c = new Money( 1, 'EUR' );

		self::assertTrue( $a->greaterThanOrEqual( $a ) );
		self::assertTrue( $a->greaterThanOrEqual( $b ) );
		self::assertTrue( $a->greaterThanOrEqual( $c ) );
		self::assertFalse( $c->greaterThanOrEqual( $a ) );
	}

	public function testCanBeComparedToAnotherMoneyObjectWithSameCurrency6(): void
	{
		$a = new Money( 1, 'EUR' );
		$b = new Money( 1, 'EUR' );
		$c = new Money( 2, 'EUR' );

		self::assertTrue( $a->lessThanOrEqual( $a ) );
		self::assertTrue( $a->lessThanOrEqual( $b ) );
		self::assertTrue( $a->lessThanOrEqual( $c ) );
		self::assertFalse( $c->lessThanOrEqual( $a ) );
	}

	public function testHasSameCurrency(): void
	{
		self::assertTrue( (new Money( 1, 'EUR' ))->hasSameCurrency( new Money( 5, 'EUR' ) ) );
		self::assertFalse( (new Money( 1, 'EUR' ))->hasSameCurrency( new Money( 1, 'USD' ) ) );
	}

	public function testExceptionIsRaisedWhenCallingEqualsWithMoneyObjectWithDifferentCurrency(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'USD' );

		$a->equals( $b );
	}

	public function testExceptionIsRaisedWhenCallingGreaterThanWithMoneyObjectWithDifferentCurrency(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'USD' );

		$a->greaterThan( $b );
	}

	public function testExceptionIsRaisedWhenCallingLessThanWithMoneyObjectWithDifferentCurrency(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'USD' );

		$a->lessThan( $b );
	}

	public function testExceptionIsRaisedWhenCallingGreaterThanOrEqualWithMoneyObjectWithDifferentCurrency(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'USD' );

		$a->greaterThanOrEqual( $b );
	}

	public function testExceptionIsRaisedWhenCallingLessThanOrEqualWithMoneyObjectWithDifferentCurrency(): void
	{
		$this->expectException( CurrencyMismatchException::class );

		$a = new Money( 1, 'EUR' );
		$b = new Money( 2, 'USD' );

		$a->lessThanOrEqual( $b );
	}

	public function testCanBeSerializedToJson(): void
	{
		self::assertEquals(
			'{"amount":"1","currencyCode":"EUR"}',
			json_encode( new Money( 1, 'EUR' ), JSON_THROW_ON_ERROR )
		);
	}

	public function testSum(): void
	{
		self::assertEquals( new Money( 100, 'EUR' ), Money::sum( new Money( 10, 'EUR' ), new Money( 50, 'EUR' ), new Money( 40, 'EUR' ) ) );
	}

	public function testMin(): void
	{
		self::assertEquals( new Money( 10, 'EUR' ), Money::min( new Money( 10, 'EUR' ), new Money( 50, 'EUR' ), new Money( 40, 'EUR' ) ) );
		self::assertEquals( new Money( 10, 'EUR' ), Money::min( new Money( 70, 'EUR' ), new Money( 50, 'EUR' ), new Money( 10, 'EUR' ), new Money( 40, 'EUR' ) ) );
	}

	public function testMax(): void
	{
		self::assertEquals( new Money( 50, 'EUR' ), Money::max( new Money( 10, 'EUR' ), new Money( 50, 'EUR' ), new Money( 40, 'EUR' ) ) );
		self::assertEquals( new Money( 70, 'EUR' ), Money::max( new Money( 70, 'EUR' ), new Money( 50, 'EUR' ), new Money( 10, 'EUR' ), new Money( 40, 'EUR' ) ) );
	}

	public function testAvg(): void
	{
		self::assertEquals( new Money( 250, 'EUR' ), Money::avg( new Money( 200, 'EUR' ), new Money( 200, 'EUR' ), new Money( 100, 'EUR' ), new Money( 500, 'EUR' ) ) );
	}
}
