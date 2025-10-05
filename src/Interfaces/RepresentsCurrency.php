<?php declare(strict_types=1);

namespace ComponoKit\Money\Interfaces;

interface RepresentsCurrency
{
	public function getIsoCode(): string;

	public function getSymbol(): string;

	public function getMinorUnitFactor(): int;

	public function equals( RepresentsCurrency $other ): bool;
}
