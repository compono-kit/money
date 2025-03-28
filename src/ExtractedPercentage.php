<?php declare(strict_types=1);

namespace Componium\Money;

use Componium\Money\Interfaces\RepresentExtractedPercentage;
use Componium\Money\Interfaces\RepresentsMoney;

class ExtractedPercentage implements RepresentExtractedPercentage
{
	public function __construct( private RepresentsMoney $percentage, private RepresentsMoney $subTotal )
	{
	}

	public function getPercentage(): RepresentsMoney
	{
		return $this->percentage;
	}

	public function getSubTotal(): RepresentsMoney
	{
		return $this->subTotal;
	}
}
