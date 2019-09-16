<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests\Fake;

use Thunder\Platenum\Enum\EnumTrait;

/**
 * @method static self FIRST()
 * @method static self SECOND($arg = null)
 * @method static self THIRD()
 * @method static self X1()
 * @method static self B()
 * @method int invalidMethod()
 * @property int $invalidProperty
 */
final class FakeEnum
{
    use EnumTrait;
}
