<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests;

use Thunder\Platenum\Exception\PlatenumException;
use Thunder\Platenum\Enum\StaticEnumTrait;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ConstantsEnumTest extends AbstractTestCase
{
    public function testMembers(): void
    {
        $members = ['FIRST' => 1, 'SECOND' => 2];
        $trait = $this->makeConstantsTraitEnum($members);
        $extends = $this->makeConstantsExtendsEnum($members);

        $this->assertSame($members, $trait::getMembersAndValues());
        $this->assertSame($members, $extends::getMembersAndValues());
    }
}
