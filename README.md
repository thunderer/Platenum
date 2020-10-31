# Platenum

[![Build Status](https://travis-ci.org/thunderer/Platenum.png?branch=master)](https://travis-ci.org/thunderer/Platenum)
[![Latest Stable Version](https://poser.pugx.org/thunderer/platenum/v/stable.svg)](https://packagist.org/packages/thunderer/platenum)
[![Total Downloads](https://poser.pugx.org/thunderer/platenum/downloads)](https://packagist.org/packages/thunderer/platenum)
[![License](https://poser.pugx.org/thunderer/platenum/license.svg)](https://packagist.org/packages/thunderer/platenum)
<br />
[![Psalm coverage](https://shepherd.dev/github/thunderer/Platenum/coverage.svg?)](https://shepherd.dev/github/thunderer/Platenum)
[![Code Coverage](https://scrutinizer-ci.com/g/thunderer/Platenum/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Platenum/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thunderer/Platenum/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Platenum/?branch=master)

Platenum provides a flexible and feature-complete solution for [enumerations (enums)](https://en.wikipedia.org/wiki/Enumerated_type) in PHP with no external dependencies. The name comes from the Latin term for a [Platinum](https://en.wikipedia.org/wiki/Platinum) chemical element.

## Installation

This library is available on Packagist and can be installed with Composer in projects supporting PHP 7.1 and above:

```
composer require thunderer/platenum
```

## Usage

Create a new class with members definition:

```php
<?php
declare(strict_types=1);
namespace X;

use Thunder\Platenum\Enum\ConstantsEnumTrait;

/**
 * @method static static ACTIVE()
 * @method static static INACTIVE()
 * @method static static SUSPENDED()
 * @method static static DISABLED()
 */
final class AccountStatusEnum
{
    private const ACTIVE = 1;
    private const INACTIVE = 2;
    private const SUSPENDED = 3;
    private const DISABLED = 4;

    use ConstantsEnumTrait;
}
```

> Tip: To enable autocomplete for the constant methods, include the [`@method` declarations](http://docs.phpdoc.org/references/phpdoc/tags/method.html) as shown in the listing above.

Members instances can be created using either constants methods, members names, or their values:

```php
$active = AccountStatusEnum::ACTIVE();
$alsoActive = AccountStatusEnum::fromMember('ACTIVE');
$stillActive = AccountStatusEnum::fromValue(1);
```

Enums can be compared using strict `===` operator or an `equals()` method:

```php
assert($active === $alsoActive);
assert(true === $active->equals($alsoActive));
```

> Note: Strict comparison `===` should be always preferred. Loose `==` comparison will also work correctly, but it has [loads of quirks](http://php.net/manual/en/language.oop5.object-comparison.php).

The `getValue()` method returns the raw value of given instance:

```php
assert(1 === $active->getValue());
```

**enum generator**

Classes can be automatically generated using built-in `bin/generate` utility. It accepts three parameters:
- location of its members (either `constants`, `docblock` or `static`),
- (fully qualified) class name, Platenum matches the namespace to your autoloading configuration and puts the file in the proper directory,
- members names with optional values where supported.

Example:

```
bin/generate constants Thunder\\Platenum\\YourEnum FOO=1,BAR=3
bin/generate docblock Thunder\\Platenum\\YourEnum FOO,BAR
bin/generate static Thunder\\Platenum\\YourEnum FOO,BAR=3
```

## Sources

There are multiple sources from which Platenum can read enumeration members. Base `EnumTrait` provides all enum functionality without any source, to be defined in a static `resolve()` method. Each source is available both as a `trait` which uses `EnumTrait` with concrete `resolve()` method implementation and an `abstract class` based on that trait. Usage of traits is recommended as target enum classes should not have any common type hint.

In this section the `BooleanEnum` class with two members (`TRUE=true` and `FALSE=false`) will be used as an example.

**class constants**

```php
final class BooleanEnum
{
    use ConstantsEnumTrait;

    private const TRUE = true;
    private const FALSE = false;
}
```

```php
final class BooleanEnum extends AbstractConstantsEnum
{
    private const TRUE = true;
    private const FALSE = false;
}
```

**class docblock**

> Note: There is no way to specify members values inside docblock, therefore all members names are also their values - in this case `TRUE='TRUE'` and `FALSE='FALSE'`.

```php
/**
 * @method static static TRUE()
 * @method static static FALSE()
 */
final class BooleanEnum
{
    use DocblockEnumTrait;
}
```

```php
/**
 * @method static static TRUE()
 * @method static static FALSE()
 */
final class BooleanEnum extends AbstractDocblockEnum {}
```

**static property**

```php
final class BooleanEnum
{
    use StaticEnumTrait;

    private static $mapping = [
        'TRUE' => true,
        'FALSE' => false,
    ];
}
```

```php
final class BooleanEnum extends AbstractStaticEnum
{
    private static $mapping = [
        'TRUE' => true,
        'FALSE' => false,
    ];
}
```

**callback**

```php
final class Currency
{
    use CallbackEnumTrait;
}
```

```php
final class Currency extends AbstractCallbackEnum
{
}
```

Unlike other types, callback enum requires initialization before creating member instances. To make it ready to use, run `initialize()` method with a callback returning `member => value` mapping (similar to `StaticEnumTrait`). This callback will be run exactly once right before creating the first member instance:

```php
Currency::initialize(fn() => [
    'PLN' => 985,
    'EUR' => 978,
    'USD' => 840,
]);
```

> NOTE: This type allows loading members and values mapping from virtually any external place (database, Redis, session, files, etc.). The only requirement for this callable is that it returns a proper `member => value` pairs.

```php
Currency::initialize(fn() => SomeClass::CONSTANT);
Currency::initialize(fn() => $database->sql('...'));
Currency::initialize(fn() => $redis->hGetAll('...'));
Currency::initialize(fn() => json_decode(file_get_contents('...')));
// etc.
```

**custom source**

> Note: The `resolve` method will be called only once when the enumeration is used for the first time.

```php
final class BooleanEnum
{
    use EnumTrait;

    private static function resolve(): array
    {
        return [
            'TRUE' => true,
            'FALSE' => false,
        ];
    }
}
```

## Exceptions

The library throws default `PlatenumException` with dedicated message for all errors happening in the enum classes. Certain situations may require a dedicated exception class and message. To redefine the exception logic, override one or more of the static methods described below:

- `throwInvalidMemberException()` used when enum receives an invalid enum **member** in any method,
- `throwInvalidValueException()` used when enum receives an invalid enum **value** in any method.

> NOTE: If the overridden method won't throw an exception, the library contains a safeguard which will still throw the default one. This way a development oversight won't hide errors in your application.

```php
final class AccountStatus
{
    use ConstantsEnumTrait;

    private const ACTIVE = 1;
    private const DISABLED = 2;

    protected static function throwInvalidMemberException(string $name): void
    {
        throw new InvalidAccountStatusException($name);
    }

    protected static function throwInvalidValueException($value): void
    {
        throw new InvalidAccountStatusValueException($value);
    }
}
```

## Persistence

Enumerations are frequently used in entities and mapped in ORMs. Register your custom Doctrine enum type by calling dedicated `PlatenumDoctrineType` static method:

```php
PlatenumDoctrineType::registerString('currency', Currency::class);
PlatenumDoctrineType::registerInteger('accountStatus', AccountStatus::class);
```

The alias provided as a first argument can be then used as a Doctrine type, as shown in the listings below (equivalent XML and PHP mapping):

```xml
<entity name="App\Entity" table="app_entity">
    <id name="id" type="bigint" column="id" />
    <field name="currencyCode" type="currency" column="currency_code" />
    <field name="status" type="accountStatus" column="status" />
</entity>
```

```php
final class Entity
{
    public static function loadMetadata(ClassMetadata $m): void
    {
        $m->setPrimaryTable(['name' => 'doctrine_entity']);

        $m->mapField(['fieldName' => 'id',     'type' => 'bigint',        'id' => true]);
        $m->mapField(['fieldName' => 'code',   'type' => 'currency',      'columnName' => 'code']);
        $m->mapField(['fieldName' => 'status', 'type' => 'accountStatus', 'columnName' => 'status']);
    }
}
```

## Reasons

There are already a few `enum` libraries in the PHP ecosystem. Why another one? There are several reasons to do so:

**Sources** Platenum allows multiple sources for enumeration members. `EnumTrait` contains all enum functions - extend it with your custom `resolve()` method to create custom source. In fact, all enumeration sources in this repository are defined this way.

**Features** Platenum provides complete feature set for all kinds of operations on enumeration members, values, comparison, transformation, and more. Look at PhpEnumerations project to see the feature matrix created during development of this library.

**Inheritance** Existing solutions use inheritance for creating enum classes:

```php
class YourEnum extends LibraryEnum
{
    const ONE = 1;
    const TWO = 2;
}
```

Enumerations should be represented as different types without an ability to be used interchangeably. Platenum leverages traits to provide complete class body, therefore `instanceof` comparison will fail as it should and there is no possibility to typehint generic `LibraryEnum` class to allow any enum instance there.

**Comparison** Creating more than one instance of certain enum value should not prohibit you from strictly comparing them like any other variable. Other solutions encourage using loose comparison (`==`) as the instances with the same values are not the same instances of their classes. This library guarantees that the same enum value instance will always be the same instance which can be strictly compared:

```php
final class YourEnum
{
    private const ONE = 1;
    private const TWO = 2;

    use EnumTrait;
}

YourEnum::ONE() === YourEnum::ONE()
YourEnum::fromValue(1) === YourEnum::ONE()
YourEnum::fromValue(1) === YourEnum::fromValue(1)
```

> Note: If you want to prove me wrong by using reflection or other opcode modifying extensions like `uopz`, then save yourself that effort, you're right and I surrender.

**Serialization**

Platenum provides correct (de)serialization solution which preserves its single member instance guarantees.

The only exception to that guarantee is when an enum instance is `unserialize()`d inside another class as PHP always creates a new object there. This can be easily mitigated by `fromInstance` replacement helper method inside `__wakeup()` method which accepts its argument by reference and automatically swaps it to a correct instance:

```php
public function __wakeup()
{
    $this->enum->fromInstance($this->enum);
}
```

Note that `equals()` method is not affected as it does not rely on the same object instance but its class and actual value inside.

# License

See LICENSE file in the main directory of this library.
