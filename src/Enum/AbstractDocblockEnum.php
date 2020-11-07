<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @psalm-template T
 * @psalm-immutable
 */
abstract class AbstractDocblockEnum implements \JsonSerializable
{
    /** @use DocblockEnumTrait<T> */
    use DocblockEnumTrait;
}
