# Swedish Entity

Validate, format and extract data for Swedish personnummer (social security numbers) and organisationsnummer (organisational numbers).

Includes validators for Laravel.

–

The benefits of this package – while not strictly according to standard – is the ability to format using borth short/long (10 or 12 characters) without or with a seperator (i.e. 11/13 characters).

Note that companies always consists of 10 characters (and an optional seperator).

This package use the excellent [personnummer/php](https://github.com/personnummer/php)-package as it's basis for the social security-handling, but with some additional attributes.

## Installation

`composer require olssonm/swedish-entity`

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

You can also automatically detect the entity type:

```php
use Olssonm\SwedishEntity\SwedishEntity;

$entity = SwedishEntity::detect('600411-8177');

var_dump(get_class($entity))
// Olssonm\SwedishEntity\Person

```

### Formatting

**Note**  
Formatting an invalid entity will result in an exception.

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

The package registrers the "entity" rule, which accepts the parameters `any`, `company`, `person`. 

```php
$this->validate($request, [
    'number' => 'required|entity:company'
]);
```

You may also omit the parameter, and the validator will fallback to `any`

```php
$this->validate($request, [
    'number' => 'required|entity'
]);
```

## Attributes

### Person

| Attribute | Comment                   | type      |
| ----------|:--------------------------|----------:|
| ssn       | The SSN of the object     | string    |
| century   | Birthyear century         | string    |
| year      | Birthyear                 | string    |
| month     | Birthmonth                | string    |
| day       | Birthday                  | string    |
| num       | The last four digits      | string    |
| check     | The checksum verifier     | string    |
| age       | Age                       | string    |
| gender    | Gender (Male/Female)      | string    |
| type      | Type of ssn <sup>*</sup>  | string    |

<sup>*</sup>*Either "Samordningsnummer" or "Personnummer"*

**Example**

```php
use Olssonm\SwedishEntity\Person;

$person = new Person('600411-8177');
$person->gender = 
```

### Company

| Attribute | Comment                   | type      |
| ----------|:--------------------------|----------:|
| org_no    | The org. no. of the object| string    |
| check     | The checksum verifier     | string    |
| type      | Type of entity<sup>*</sup>| string    |

<sup>*</sup>*One of the following: "Dödsbon", "Stat, landsting och kommuner", "Aktiebolag", "Enkelt bolag", "Ekonomiska föreningar", "Ideella föreningar och stiftelser" and "Handelsbolag, kommanditbolag och enkla bolag".*