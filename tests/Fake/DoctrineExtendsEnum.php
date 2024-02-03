<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests\Fake;

/**
 * @method static static ONE()
 * @method static static TWO()
 */
final class DoctrineExtendsEnum extends DoctrineExtendsBaseEnum
{
    private const ONE = 'one';
    private const TWO = 'two';
}
