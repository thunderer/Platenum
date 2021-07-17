<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests;

use Thunder\Platenum\Enum\CallbackEnumTrait;
use Thunder\Platenum\Exception\PlatenumException;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class AttributeEnumTest extends AbstractTestCase
{
    public function testMembers(): void
    {
        PHP_VERSION_ID < 80000 && $this->markTestSkipped('Requires PHP 8.0');

        $members = ['FIRST' => 1, 'SECOND' => 2];
        $trait = $this->makeAttributeTraitEnum($members);
        $extends = $this->makeAttributeExtendsEnum($members);

        $expected = ['FIRST' => 1, 'SECOND' => 2];
        $this->assertSame($expected, $trait::getMembersAndValues());
        $this->assertSame($expected, $extends::getMembersAndValues());
    }
}
