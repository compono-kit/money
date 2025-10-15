<?php declare(strict_types=1);

namespace ComponoKit\Money\Tests\Unit;

use ComponoKit\Money\Exceptions\InvalidRatioException;
use ComponoKit\Money\Helpers\AmountAllocator;
use PHPUnit\Framework\TestCase;

class AmountAllocatorTest extends TestCase
{
	public function testAllocateToTargetsEvenDistribution(): void
	{
		$result = AmountAllocator::allocateToTargets( 100, 4 );

		$this->assertCount( 4, $result );
		$this->assertSame( [ 25, 25, 25, 25 ], $result );
	}

	public function testAllocateToTargetsUnevenDistribution(): void
	{
		$result = AmountAllocator::allocateToTargets( 10, 3 );

		$this->assertCount( 3, $result );
		$this->assertSame( 10, array_sum( $result ) );
	}

	public function testAllocateToTargetsSingleTarget(): void
	{
		$result = AmountAllocator::allocateToTargets( 50, 1 );

		$this->assertCount( 1, $result );
		$this->assertSame( [ 50 ], $result );
	}

	public function testAllocateToTargetsZeroAmount(): void
	{
		$result = AmountAllocator::allocateToTargets( 0, 3 );

		$this->assertCount( 3, $result );
		$this->assertSame( [ 0, 0, 0 ], $result );
	}

	public function testAllocateToTargetsInvalidTargetCount(): void
	{
		$this->expectException( InvalidRatioException::class );
		AmountAllocator::allocateToTargets( 100, 0 );
	}

	public function testCanBeAllocatedByRatios(): void
	{
		$result = AmountAllocator::allocateByRatios( 5, [ 3, 7 ] );

		self::assertEquals( [ 2, 3 ], $result );
	}
}
