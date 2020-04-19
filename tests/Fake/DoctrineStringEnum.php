<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests\Fake;

use Thunder\Platenum\Enum\ConstantsEnumTrait;

/**
 * @method static static ONE()
 * @method static static TWO()
 */
final class DoctrineStringEnum
{
    use ConstantsEnumTrait;

    private const ONE = 'one';
    private const TWO = 'two';
}
