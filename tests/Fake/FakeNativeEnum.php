<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests\Fake;

use Thunder\Platenum\Enum\NativeEnumTrait;

/**
 * @method static self FIRST()
 * @method static self SECOND($arg = null)
 * @method static self THIRD()
 * @method static self X1()
 * @method static self B()
 * @method int invalidMethod()
 * @property int $invalidProperty
 */
enum FakeNativeEnum: int
{
    case FIRST = 1;
    case SECOND = 2;
    case THIRD = 3;
    case X1 = 4;
    case B = 5;

    use NativeEnumTrait;

    public function __toString(): string
    {
        return 'x';
    }
}
