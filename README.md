# Money

Wrapper for moneyphp/money with additional Interfaces.

* **Bcmath-Extension required**
* **Intl-Extension required for IntlFormatter**

## Inhalt

* [Basics](#basics)
* [Comparisons](#comparisons)
* [Allocation](#allocation)
* [Value added tax calculation](#value-added-tax-calculation)
* [Json](#json)
* [Formatting](#formatting)
* [Docker](#docker)

## Basics

````PHP
(new Money( 5000, 'EUR' ))->getAmount(); //5000
(new Money( 5000, 'EUR' ))->add( new Money( 99, 'EUR' ) )->getAmount(); //5099
(new Money( 5000, 'EUR' ))->subtract( new Money( 99, 'EUR' ) )->getAmount(); //4901
(new Money( 5000, 'EUR' ))->multiply( 2 )->getAmount(); //10000
(new Money( 5000, 'EUR' ))->divide( 2 )->getAmount(); //2500

(new Money( 5000, 'EUR' ))->mod( new Money( 4000, 'EUR' ) )->getAmount(); //1000
(new Money( -5000, 'EUR' ))->absolute()->getAmount(); //5000
(new Money( 5000, 'EUR' ))->negate()->getAmount(); //-5000

(new Money( 5000, 'EUR' ))->ratioOf( new Money( 10000, 'EUR' ) ); //0.5
(new Money( 5000, 'EUR' ))->ratioOf( new Money( 2500, 'EUR' ) ); //2

(new Money( 5000, 'EUR' ))->getCurrencyCode() ; //EUR
(new Money( 5000, 'EUR' ))->hasSameCurrency( new Money( 5000, 'USD' ) ) ; //false
````

## Comparisons

````PHP
(new Money( 1000, 'EUR' ))->greaterThan( new Money( 2000, 'EUR' ) ); //false
(new Money( 1000, 'EUR' ))->lessThan( new Money( 2000, 'EUR' ) ); //true
(new Money( 1000, 'EUR' ))->equals( new Money( 1000, 'EUR' ) ); //true
(new Money( 1000, 'EUR' ))->lessThanOrEqual( new Money( 1000, 'EUR' ) ); //true
(new Money( 1000, 'EUR' ))->lessThanOrEqual( new Money( 1500, 'EUR' ) ); //true
(new Money( 1000, 'EUR' ))->greaterThanOrEqual( new Money( 1000, 'EUR' ) ); //true
(new Money( 1000, 'EUR' ))->greaterThanOrEqual( new Money( 500, 'EUR' ) ); //true

(new Money( 1000, 'EUR' ))->equals( new Money( 4000, 'USD' ) ); //throws CurrencyMismatchException
````

````PHP
(new Money( -1000, 'EUR' ))->isNegative(); //true
(new Money( 1000, 'EUR' ))->isPositive(); //true
(new Money( 0, 'EUR' ))->isZero(); //true
````

## Aggregation

````PHP
Money::sum( new Money( 1000, 'EUR' ), new Money( 2000, 'EUR' ), new Money( 4000, 'EUR' ) )->getAmount(); //7000
Money::avg( new Money( 5000, 'EUR' ), new Money( 2000, 'EUR' ), new Money( 8000, 'EUR' ) )->getAmount(); //3000
Money::min( new Money( 1000, 'EUR' ), new Money( 2000, 'EUR' ), new Money( 4000, 'EUR' ) )->getAmount(); //1000
Money::max( new Money( 1000, 'EUR' ), new Money( 2000, 'EUR' ), new Money( 4000, 'EUR' ) )->getAmount(); //4000
````

## Allocation

````PHP
$allocatedMoney = (new Money( 99, 'EUR' ))->allocateToTargets( 5 );
$allocatedMoney[0]->getAmount(); //20
$allocatedMoney[1]->getAmount(); //20
$allocatedMoney[2]->getAmount(); //20
$allocatedMoney[3]->getAmount(); //20
$allocatedMoney[4]->getAmount(); //19

$allocatedMoney = (new Money( 5000, 'EUR' ))->allocateByRatios( [ 70, 30 ] );
printf( "70%% of 5000 = %d", $allocatedMoney[0]->getAmount() ); //70% of 5000 = 3500
printf( "30%% of 5000 = %d", $allocatedMoney[1]->getAmount() ); //30% of 5000 = 1500
````

## Value added tax calculation

````PHP
$extractedPercentage = (new Money( 5000, 'EUR' ))->extractPercentage( 19 ); //19% Mwst.-Satz, 5000 = Bruttobetrag
printf(
	"%d + %d = 5000",
	$extractedPercentage->getSubTotal()->getAmount(),
	$extractedPercentage->getPercentage()->getAmount()
); // 4202 + 798 = 5000

$extractedPercentage->getSubTotal(); //Nettobetrag
$extractedPercentage->getPercentage(); //Mwst.-Betrag
````

## Json

````PHP
json_encode( (new Money( 5000, 'EUR' )) ); //{ "amount": "5000", "currency": "EUR" }
````

## Formatting

````PHP
DecimalMoneyFormatter::format( new Money( 0, 'EUR' ) ); //0.00
DecimalMoneyFormatter::format( new Money( 50090090, 'EUR' ) ); //500900.90
DecimalMoneyFormatter::format( new Money( -50090090, 'EUR' ) ); //-500900.90

IntlMoneyFormatter::format( new Money( 0, 'EUR' ), 'en_US' ); //€0.00
IntlMoneyFormatter::format( new Money( 0, 'EUR' ), 'de_DE' ); //0,00 €
IntlMoneyFormatter::format( new Money( 50090090, 'EUR' ), 'en_US' ); //€500,900.90
IntlMoneyFormatter::format( new Money( 50090090, 'EUR' ), 'de_DE' ); //500.900,90 €

IntlDecimalFormatter::format( new Money( 0, 'EUR' ), 'de_DE' ); //0
IntlDecimalFormatter::format( new Money( 50090090, 'EUR' ), 'en_US' ); //500,900.9
IntlDecimalFormatter::format( new Money( 50090090, 'EUR' ), 'de_DE' ); //500.900,9
````

## Docker

* `docker-compose build --build-arg GITHUB_TOKEN="{TOKEN}"`
* `docker-compose up -d`
* `docker-compose run --rm money_composer update -vvv` or `docker exec -it money_composer sh` and run there `composer update -vvv`
