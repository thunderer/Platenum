<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @psalm-template T
 * @psalm-immutable
 */
abstract class AbstractStaticEnum implements \JsonSerializable
{
    /** @use StaticEnumTrait<T> */
    use StaticEnumTrait;

    /** @var array */
    protected static $mapping = [];
}
