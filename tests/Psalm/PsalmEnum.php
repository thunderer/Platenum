<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests\Psalm;

use Thunder\Platenum\Enum\AbstractConstantsEnum;
use Thunder\Platenum\Enum\ConstantsEnumTrait;

final class PsalmEnum
{
}

/**
 * @method static static FIRST()
 * @method static static SECOND()
 * @psalm-template T of self::*
 * @psalm-immutable
 */
final class PsalmConstantsTraitEnum
{
    /** @use ConstantsEnumTrait<T> */
    use ConstantsEnumTrait;

    public const FIRST = 1;
    public const SECOND = 2;
}

PsalmConstantsTraitEnum::FIRST();
PsalmConstantsTraitEnum::SECOND();
PsalmConstantsTraitEnum::fromMember('THIRD');
PsalmConstantsTraitEnum::fromValue(4);

/**
 * @method static static FIRST()
 * @method static static SECOND()
 * @psalm-template T of self::*
 * @psalm-immutable
 */
final class PsalmConstantsExtendsEnum extends AbstractConstantsEnum
{
    public const FIRST = 1;
    public const SECOND = 2;
}

PsalmConstantsExtendsEnum::FIRST();
PsalmConstantsExtendsEnum::SECOND();
PsalmConstantsExtendsEnum::fromMember('THIRD');
PsalmConstantsExtendsEnum::fromValue(4);