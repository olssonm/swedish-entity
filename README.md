# Swedish Entity

Validate, format and extract data for Swedish personnummer (social security numbers) and organisationsnummer (organisational numbers).

This package also handles the temporary personal identity number known as "Samordningsnummer" (a.k.a. coordination number).

Includes [validators for Laravel](#laravel-validators).

–

The benefits of this package – while not always strictly according to the standard – is the ability to format using both short/long (10 or 12 characters) without or with a seperator (i.e. 11/13 characters).

Note that companies always consists of 10/11 characters (with or without an optional seperator).

This package use the excellent [personnummer/php](https://github.com/personnummer/php)-package as it's basis for the social security-handling, but with some additional attributes.

## Installation

```
composer require olssonm/swedish-entity
```

## Usage

### Validation

```php
use Olssonm\SwedishEntity\Person;

(new Person('600411-8177'))->valid()
// true
```

```php
use Olssonm\SwedishEntity\Company;

(new Company('556016-0680'))->valid()
// true
```

**Automatically detect the entity type:**

*⚠️ If the `detect`-method fails, an `Olssonm\SwedishEntity\Exceptions\DetectException` will be thrown.*

```php
use Olssonm\SwedishEntity\Entity;

$entity = Entity::detect('600411-8177');

var_dump(get_class($entity))
// Olssonm\SwedishEntity\Person

```

### Formatting

*⚠️ Formatting an invalid entity will result in an exception. You should make sure to validate it beforehand.*

#### Person

```php
use Olssonm\SwedishEntity\Person;

(new Person('600411-8177'))->format($characters = 12, $seperator = true)
// 19600411-8177

(new Person('100411+8177'))->format($characters = 12, $seperator = true)
// 19100411+8177

(new Person('19100411+8177'))->format($characters = 10, $seperator = false)
// 1004118177
```

#### Company

```php
use Olssonm\SwedishEntity\Company;

(new Person('5560160680'))->format($seperator = true)
// 556016-0680

(new Person('556016-0680'))->format($seperator = false)
// 5560160680
```

### Laravel validators

The package registrers the "entity" rule, which accepts the parameters `any`, `company` or `person`. 

```php
$this->validate($request, [
    'number' => 'required|entity:company'
]);
```

You may also omit the parameter and the validator will fallback to `any`

```php
$this->validate($request, [
    'number' => 'required|entity'
]);
```

Custom messages

```php
$validator = Validator::make($request->all(), [
    'number' => 'required|entity:person'
], [
    'number.entity' => "Invalid personnummer!"
]);
```

## Attributes

### Person

| Attribute | Comment                   | type      |
| ----------|:--------------------------|----------:|
| ssn       | The SSN of the entity     | string    |
| century   | Birthyear century         | string    |
| year      | Birthyear                 | string    |
| month     | Birthmonth                | string    |
| day       | Birthday                  | string    |
| num       | The "last four digits"    | string    |
| check     | The checksum verifier     | string    |
| age       | Age                       | string    |
| birthday  | Entitys birthday          | DateTime  |
| gender    | Gender (Male/Female)      | string    |
| type      | Type of ssn <sup>*</sup>  | string    |

<sup>*</sup>*Either "Samordningsnummer" or "Personnummer"*

**Example**

```php
use Olssonm\SwedishEntity\Person;

$person = new Person('600411-8177');
$person->gender;
// Male
```

### Company

| Attribute | Comment                   | type      |
| ----------|:--------------------------|----------:|
| org_no    | The org. no. of the entity| string    |
| check     | The checksum verifier     | string    |
| type      | Type of entity<sup>*</sup>| string    |

<sup>*</sup>*One of the following: "Dödsbon", "Stat, landsting och kommuner", "Aktiebolag", "Enkelt bolag", "Ekonomiska föreningar", "Ideella föreningar och stiftelser" and "Handelsbolag, kommanditbolag och enkla bolag".*

**Example**

```php
use Olssonm\SwedishEntity\Company;

$company = new Company('212000-1355');
$company->type;
// Stat, landsting och kommuner
```

### Gotcha moments

#### Enskild firma
EF (Enskild firma) – while technically a company, uses the proprietors personnummer. Therefore that number will not validate as company. Instead of using a custom solution for this (as Creditsafe, Bisnode and others do, by adding additional numbers/characters to the organisational number/social security number), a way to handle this would be:

- Work with 10 digits when expecting both people and companies (preferably with a seperator)
- Use the `detect`-method to automatically validate both types

If you need to after the validation check type;

```php
use Olssonm\SwedishEntity\Entity;
use Olssonm\SwedishEntity\Person;
use Olssonm\SwedishEntity\Company;
use Olssonm\SwedishEntity\Exceptions\DetectException

try {
    $entity = Entity::detect('600411-8177');
} catch (DetectException $e) {
    // Handle exception
}

// PHP < 8
if(get_class($entity) == Person::class) {
    // Do stuff for person
} elseif(get_class($entity) == Company::class) {
    // Do stuff for company
}

// PHP 8
$result = match($entity::class) {
    Person::class => fn() {}, // Do stuff for person,
    Company::class => fn() {} // Do stuff for company
}
```

## License

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.

© 2020 [Marcus Olsson](https://marcusolsson.me).