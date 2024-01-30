<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @template TMember
 * @template TValue
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
abstract class AbstractDocblockEnum implements \JsonSerializable
{
    /** @use DocblockEnumTrait<TMember,TValue> */
    use DocblockEnumTrait;
}
