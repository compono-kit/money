<?php declare(strict_types=1);

namespace Componium\Money\Tests\Unit\Formatters;

use Componium\Money\Formatters\DecimalMoneyFormatter;
use Componium\Money\Interfaces\RepresentsMoney;
use Componium\Money\Money;
use PHPUnit\Framework\TestCase;

class DecimalMoneyFormatterTest extends TestCase
{
	public function MoneyDataProvider(): array
	{
		return [
			[ new Money( 0, 'EUR' ), '0.00' ],
			[ new Money( 100, 'EUR' ), '1.00' ],
			[ new Money( 123456789, 'EUR' ), '1234567.89' ],
			[ new Money( -123456789, 'EUR' ), '-1234567.89' ],
			[ new Money( 5990, 'EUR' ), '59.90' ],
			[ new Money( -5990, 'EUR' ), '-59.90' ],
		];
	}

	/**
	 * @dataProvider MoneyDataProvider
	 *
	 * @param RepresentsMoney $money
	 * @param string          $expectedOutput
	 */
	public function testIfFormattingReturnsExpectedOutput( RepresentsMoney $money, string $expectedOutput ): void
	{
		self::assertEquals( $expectedOutput, DecimalMoneyFormatter::format( $money ) );
	}
}
