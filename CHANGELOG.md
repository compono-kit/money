# CHANGELOG

## [3.0.0] - 2026-10-14

### Changed
* [BC] Requires PHP 8.5 and `compono-kit/money-interfaces` ^2.0
* [BC] `multiply()`, `divide()`, `extractPercentage()` and `fromFloat()` expect a `\RoundingMode` instead of an integer; all modes of the enum are supported, e.g. `\RoundingMode::TowardsZero`
* [BC] Arithmetic methods return `static`
* [BC] Results outside the integer range throw `AmountOutOfRangeException` instead of failing with a type error or being cast silently
* [BC] `allocateToTargets()` and `allocateByRatios()` throw `InvalidRatioException` for less than one target and for ratios that are no integers
* `ExtractedPercentage` implements `RepresentsExtractedPercentage`
* `greaterThanOrEqual()` and `lessThanOrEqual()` check the currencies only once

### Added
* `AmountOutOfRangeException`
* Composer script `test`

### Removed
* [BC] `InvalidRoundingModeException`, invalid rounding modes are prevented by the `\RoundingMode` type
* Requirement of `ext-json`, which is always available since PHP 8.0

### Fixed
* `Currency::equals()` read the ISO code of other currency implementations from a private property
* `hasSameCurrency()` and the currency check of all arithmetic methods called `getCurrencyCode()`, which is not part of `RepresentsMoney`
* `fromFloat()` did not validate the rounding mode

---

## [2.0.0] - 2025-10-19

### Changed
* Uses now PHP 8.3 

---

## [1.0.0] - 2025-10-19

### Added
* Initial release
