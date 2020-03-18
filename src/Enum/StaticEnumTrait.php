<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

use Thunder\Platenum\Exception\PlatenumException;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
trait StaticEnumTrait
{
    use EnumTrait;

    final private static function resolve(): array
    {
        $class = static::class;
        // reflection instead of property_exists because of PHP 7.4 bug #78632
        // @see https://bugs.php.net/bug.php?id=78632
        if(false === (new \ReflectionClass($class))->hasProperty('mapping')) {
            throw PlatenumException::fromMissingMappingProperty($class);
        }

        return static::$mapping;
    }
}
