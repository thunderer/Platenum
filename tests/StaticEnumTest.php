<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests;

use Thunder\Platenum\Exception\PlatenumException;
use Thunder\Platenum\Enum\StaticEnumTrait;
use Thunder\Platenum\Tests\Fake\FakeEnum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class StaticEnumTest extends AbstractTestCase
{
    public function testMembers(): void
    {
        $members = ['FIRST' => 1, 'SECOND' => 2];
        $trait = $this->makeStaticTraitEnum($members);
        $extends = $this->makeStaticExtendsEnum($members);

        $this->assertSame($members, $trait::getMembersAndValues());
        $this->assertSame($members, $extends::getMembersAndValues());
    }

    public function testExceptionMissingProperty(): void
    {
        /** @var FakeEnum $enum */
        $enum = $this->computeUniqueClassName('X');
        eval('class '.$enum.' { use '.StaticEnumTrait::class.'; }');

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum '.$enum.' requires static property $mapping with members definitions.');
        $enum::FIRST();
    }
}
