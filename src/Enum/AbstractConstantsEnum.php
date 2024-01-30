<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @template TMember
 * @template TValue
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
abstract class AbstractConstantsEnum implements \JsonSerializable
{
    /** @use ConstantsEnumTrait<TMember,TValue> */
    use ConstantsEnumTrait;
}
