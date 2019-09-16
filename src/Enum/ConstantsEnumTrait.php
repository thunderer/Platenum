<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
trait ConstantsEnumTrait
{
    use EnumTrait;

    final private static function resolve(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}
