<?php declare(strict_types=1);

namespace ComponoKit\Money\Tests\Unit;

use ComponoKit\Money\Utilities\MoneyAggregator;
use ComponoKit\Money\Money;
use ComponoKit\Money\Tests\Unit\Traits\BuildingCurrencies;
use PHPUnit\Framework\TestCase;

class MoneyAggregatorTest extends TestCase
{
	use BuildingCurrencies;

	public function testSum(): void
	{
		self::assertEquals(
			new Money( 100, $this->buildEurCurrency() ),
			MoneyAggregator::sum(
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
			MoneyAggregator::min(
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 50, $this->buildEurCurrency() ),
				new Money( 40, $this->buildEurCurrency() )
			)
		);
		self::assertEquals(
			new Money( 10, $this->buildEurCurrency() ),
			MoneyAggregator::min(
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
			MoneyAggregator::max(
				new Money( 10, $this->buildEurCurrency() ),
				new Money( 50, $this->buildEurCurrency() ),
				new Money( 40, $this->buildEurCurrency() )
			)
		);
		self::assertEquals(
			new Money( 70, $this->buildEurCurrency() ),
			MoneyAggregator::max(
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
			MoneyAggregator::avg(
				new Money( 200, $this->buildEurCurrency() ),
				new Money( 200, $this->buildEurCurrency() ),
				new Money( 100, $this->buildEurCurrency() ),
				new Money( 500, $this->buildEurCurrency() )
			)
		);
	}
}
