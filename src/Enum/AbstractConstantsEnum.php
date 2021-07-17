<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @psalm-template T
 * @psalm-immutable
 */
abstract class AbstractConstantsEnum implements \JsonSerializable
{
    /** @use ConstantsEnumTrait<T> */
    use ConstantsEnumTrait;
}
