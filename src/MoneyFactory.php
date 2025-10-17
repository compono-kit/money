<?php declare(strict_types=1);

namespace ComponoKit\Money;

use ComponoKit\Money\Interfaces\BuildsMoneys;
use ComponoKit\Money\Interfaces\RepresentsCurrency;
use ComponoKit\Money\Interfaces\RepresentsMoney;

class MoneyFactory implements BuildsMoneys
{
	public function build( int $amount, RepresentsCurrency $currency ): RepresentsMoney
	{
		return new Money( $amount, $currency );
	}
}
