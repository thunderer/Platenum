<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests;

use Thunder\Platenum\Enum\DocblockEnumTrait;
use Thunder\Platenum\Exception\PlatenumException;
use Thunder\Platenum\Tests\Fake\FakeEnum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class DocblockEnumTest extends AbstractTestCase
{
    public function testMembers(): void
    {
        $members = ['FIRST' => 1, 'SECOND' => 2];
        $trait = $this->makeDocblockTraitEnum($members);
        $extends = $this->makeDocblockExtendsEnum($members);

        $expected = ['FIRST' => 'FIRST', 'SECOND' => 'SECOND'];
        $this->assertSame($expected, $trait::getMembersAndValues());
        $this->assertSame($expected, $extends::getMembersAndValues());
    }

    public function testExceptionDocblockNotPresent(): void
    {
        /** @var FakeEnum $enum */
        $enum = $this->computeUniqueClassName('X');
        eval('class '.$enum.' { use '.DocblockEnumTrait::class.'; }');

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum '.$enum.' does not have a docblock with member definitions.');
        $enum::FIRST();
    }

    public function testExceptionDocblockWithoutMembers(): void
    {
        /** @var FakeEnum $enum */
        $enum = $this->computeUniqueClassName('X');
        eval('/**
         * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
         */
        class '.$enum.' { use '.DocblockEnumTrait::class.'; }');

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum '.$enum.' contains docblock without any member definitions.');
        $enum::FIRST();
    }

    public function testExceptionMalformedDocblockMembers(): void
    {
        /** @var FakeEnum $enum */
        $enum = $this->computeUniqueClassName('X');
        eval('/**
         * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
         *
         * @method static self FIRST()
         * @method static self INV*ALID()
         */
        class '.$enum.' { use '.DocblockEnumTrait::class.'; }');

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum '.$enum.' contains malformed docblock member definitions.');
        $enum::FIRST();
    }
}
