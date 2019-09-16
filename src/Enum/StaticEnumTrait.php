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
        if(false === property_exists($class, 'mapping')) {
            throw PlatenumException::fromMissingMappingProperty($class);
        }

        return static::$mapping;
    }
}
