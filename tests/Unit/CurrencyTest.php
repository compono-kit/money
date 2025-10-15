<?php declare(strict_types=1);

namespace ComponoKit\Money\Tests\Unit;

use ComponoKit\Money\Currency;
use ComponoKit\Money\Exceptions\InvalidCurrencyException;
use PHPUnit\Framework\TestCase;

class CurrencyTest extends TestCase
{
	public function testConstructorValidCurrency()
	{
		$currency = new Currency( 'eur', '€', 100 );

		$this->assertSame( 'EUR', $currency->getIsoCode() );
		$this->assertSame( '€', $currency->getSymbol() );
		$this->assertSame( 100, $currency->getMinorUnitFactor() );
	}

	public function testConstructorThrowsExceptionForInvalidIsoCode()
	{
		$this->expectException( InvalidCurrencyException::class );
		$this->expectExceptionMessage( 'ISO code must be exactly 3 letters (A–Z)' );

		new Currency( 'EURO', '€', 100 );
	}

	public function testConstructorThrowsExceptionForNegativeMinorUnitFactor()
	{
		$this->expectException( InvalidCurrencyException::class );
		$this->expectExceptionMessage( 'Minor unit factor must be greater than 0' );

		new Currency( 'USD', '$', 0 );
	}

	public function testGetIsoCode()
	{
		$currency = new Currency( 'USD', '$', 100 );
		$this->assertSame( 'USD', $currency->getIsoCode() );
	}

	public function testGetSymbol()
	{
		$currency = new Currency( 'JPY', '¥', 1 );
		$this->assertSame( '¥', $currency->getSymbol() );
	}

	public function testGetMinorUnitFactor()
	{
		$currency = new Currency( 'CHF', 'Fr.', 100 );
		$this->assertSame( 100, $currency->getMinorUnitFactor() );
	}

	public function testEqualsReturnsTrueForSameIsoCode()
	{
		$currency1 = new Currency( 'EUR', '€', 100 );
		$currency2 = new Currency( 'eur', '€', 100 );

		$this->assertTrue( $currency1->equals( $currency2 ) );
	}

	public function testEqualsReturnsFalseForDifferentIsoCode()
	{
		$currency1 = new Currency( 'EUR', '€', 100 );
		$currency2 = new Currency( 'USD', '$', 100 );

		$this->assertFalse( $currency1->equals( $currency2 ) );
	}
}
