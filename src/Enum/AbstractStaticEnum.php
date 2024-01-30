<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @template TMember
 * @template TValue
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
abstract class AbstractStaticEnum implements \JsonSerializable
{
    /** @use StaticEnumTrait<TMember,TValue> */
    use StaticEnumTrait;

    /** @var array */
    protected static $mapping = [];
}
