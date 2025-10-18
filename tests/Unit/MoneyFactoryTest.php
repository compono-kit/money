<?php declare(strict_types=1);

namespace ComponoKit\Money\Tests\Unit;

use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;
use ComponoKit\Money\MoneyFactory;
use PHPUnit\Framework\TestCase;

class MoneyFactoryTest extends TestCase
{
	public function testBuildReturnsMoneyInstanceWithCorrectAmountAndCurrency(): void
	{
		$expectedCurrency = $this->createMock( RepresentsCurrency::class );
		$expectedAmount   = 1234;

		$factory = new MoneyFactory( $expectedCurrency );
		$money   = $factory->build( $expectedAmount );

		$this->assertInstanceOf( RepresentsMoney::class, $money );
		$this->assertSame( $expectedAmount, $money->getAmount() );
		$this->assertSame( $expectedCurrency, $money->getCurrency() );
	}
}
