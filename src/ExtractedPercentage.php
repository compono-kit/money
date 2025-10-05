<?php declare(strict_types=1);

namespace ComponoKit\Money;

use ComponoKit\Money\Interfaces\RepresentExtractedPercentage;
use ComponoKit\Money\Interfaces\RepresentsMoney;

class ExtractedPercentage implements RepresentExtractedPercentage
{
	public function __construct( private readonly RepresentsMoney $percentage, private readonly RepresentsMoney $subTotal )
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
