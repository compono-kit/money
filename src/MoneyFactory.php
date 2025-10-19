<?php declare(strict_types=1);

namespace ComponoKit\Money;

use ComponoKit\Money\Interfaces\BuildsMoneys;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;

class MoneyFactory implements BuildsMoneys
{
	public function __construct( private readonly RepresentsCurrency $currency )
	{
	}

	public function build( int $amount ): RepresentsMoney
	{
		return new Money( $amount, $this->currency );
	}
}
