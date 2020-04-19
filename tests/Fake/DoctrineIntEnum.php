<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests\Fake;

use Thunder\Platenum\Enum\ConstantsEnumTrait;

/**
 * @method static static FIRST()
 * @method static static SECOND()
 */
final class DoctrineIntEnum
{
    use ConstantsEnumTrait;

    private const FIRST = 1;
    private const SECOND = 2;
}
