<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @template TMember
 * @template TValue
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
trait ConstantsEnumTrait
{
    /** @use EnumTrait<TMember,TValue> */
    use EnumTrait;

    private static function resolve(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}
