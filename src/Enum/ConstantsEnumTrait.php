<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @psalm-template T
 * @psalm-immutable
 */
trait ConstantsEnumTrait
{
    /** @use EnumTrait<T> */
    use EnumTrait;

    private static function resolve(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}
