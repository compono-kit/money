<?php declare(strict_types=1);

namespace Componium\Money\Tests\Unit\Formatters;

use Componium\Money\Formatters\IntlDecimalFormatter;
use Componium\Money\Interfaces\RepresentsMoney;
use Componium\Money\Money;
use PHPUnit\Framework\TestCase;

class IntlDecimalFormatterTest extends TestCase
{
	public function MoneyDataProvider(): array
	{
		return [
			[ new Money( 0, 'EUR' ), 'de_DE', '0' ],
			[ new Money( 100, 'EUR' ), 'de_DE', '1' ],
			[ new Money( 10000000000, 'EUR' ), 'de_DE', '100.000.000' ],
			[ new Money( 123456789, 'EUR' ), 'en_US', '1,234,567.89' ],
			[ new Money( -123456789, 'EUR' ), 'en_US', '-1,234,567.89' ],
			[ new Money( 5990, 'USD' ), 'en_US', '59.9' ],
			[ new Money( -5990, 'USD' ), 'en_US', '-59.9' ],
			[ new Money( 5990, 'EUR' ), 'de_DE', '59,9' ],
			[ new Money( -5990, 'EUR' ), 'de_DE', '-59,9' ],
			[ new Money( 590090, 'EUR' ), 'en_US', '5,900.9' ],
			[ new Money( -590090, 'EUR' ), 'en_US', '-5,900.9' ],
			[ new Money( 590090, 'EUR' ), 'de_DE', '5.900,9' ],
			[ new Money( -590090, 'EUR' ), 'de_DE', '-5.900,9' ],
		];
	}

	/**
	 * @dataProvider MoneyDataProvider
	 *
	 * @param RepresentsMoney $money
	 * @param string          $locale
	 * @param string          $expectedOutput
	 */
	public function testIfFormattingReturnsExpectedOutput( RepresentsMoney $money, string $locale, string $expectedOutput ): void
	{
		self::assertEquals( $expectedOutput, IntlDecimalFormatter::format( $money, $locale ) );
	}
}
