# Change Log

All notable changes to `hansel23/money` will be documented in this file. 
Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [5.0.0] - 2025-03-28

### Changed
* **[BC break]** Upgrade to php 8.4
* **[BC break]** Change namespace from Hansel23 to Componium
* **[BC break]** Change interfaces by removing RepresentsCurrency (instead use string only) and renaming currency to currencyCode

## [4.0.0] - 2021-11-04

### Changed
* Upgrade to php 8
* Use moneyphp/money ^4.0.0

### Removed
* **[BC break]** Static method `fromString` (default constructor can be used instead)
* MoneyFormattingException thrown in intl formatters, because now it is handled in the base money class internally by `assert`

## [3.1.0] - 2021-10-28

### Added
* Formatters:
  * `IntlDecimalFormatter` (needs intl-ext)
  * `IntlMoneyFormatter` (needs intl-ext)

## [3.0.0] - 2021-10-27

### Added
* Methods:
  * `divide`
  * `mod`
  * `absolute`
  * `negate`
  * `ratioOf`
  * `isZero`
  * `isPositive`
  * `isNegative`
  * `hasSameCurrency`
  

* Static methods:
  * `fromMoney`
  * `min`
  * `max`
  * `avg`
  * `sum`

### Changed
* Use `moneyphp/money`

### Removed
* Method `getConvertedAmount`, use Formatters instead

## [2.0.0] - 2021-06-10

### Added
* PHP 8 support
  
### Removed
* PHP 5 support
* PHP 7 support

## [1.0.0] - 2016-05-20

### Added
* Initial release

### Changes to the original project by [Sebastian Bergmann](https://github.com/sebastianbergmann/money)

* Added PSR-4 compatibility for composer, removed own autoload script
* Moved money sub classes to namespace `Fortuneglobe\Money\Currencies`
* Moved exceptions to namespace `Fortuneglobe\Money\Exceptions`
* Moved interfaces to namespace `Fortuneglobe\Money\Interfaces`
* Renamed interface `Formatter` to `FormatsMoney`
* Moved unit tests to namespace `Fortuneglobe\Money\Tests\Unit`

[5.0.0]: https://github.com/hansel23/money/compare/4.0.0...5.0.0
[4.0.0]: https://github.com/hansel23/money/compare/3.1.0...4.0.0
[3.1.0]: https://github.com/hansel23/money/compare/3.0.0...3.1.0
[3.0.0]: https://github.com/hansel23/money/compare/2.0.0...3.0.0
[2.0.0]: https://github.com/hansel23/money/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/hansel23/money/releases/tag/v1.0.0
