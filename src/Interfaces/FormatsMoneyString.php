<?php declare(strict_types=1);

namespace ComponoKit\Money\Interfaces;

interface FormatsMoneyString
{
	public function formatString( RepresentsMoney $money ): string;
}
