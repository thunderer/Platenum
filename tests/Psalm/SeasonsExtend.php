<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests\Psalm;

use Thunder\Platenum\Enum\AbstractConstantsEnum;

/**
 * @method static static SPRING()
 * @method static static SUMMER()
 * @method static static AUTUMN()
 * @method static static WINTER()
 *
 * @extends AbstractConstantsEnum<'SPRING'|'SUMMER'|'AUTUMN'|'WINTER',SeasonsExtend::*>
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class SeasonsExtend extends AbstractConstantsEnum
{
    private const SPRING = 1;
    private const SUMMER = 2;
    private const AUTUMN = 3;
    private const WINTER = 4;
}

$spring = SeasonsExtend::SPRING();
/** @psalm-suppress InvalidArgument */
$spring->hasMember('INVALID');
/** @psalm-suppress InvalidArgument */
$spring->hasValue(9);
