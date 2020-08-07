<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests;

use Thunder\Platenum\Enum\CallbackEnumTrait;
use Thunder\Platenum\Exception\PlatenumException;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class CallbackEnumTest extends AbstractTestCase
{
    public function testMembers(): void
    {
        $members = ['FIRST' => 1, 'SECOND' => 2];
        $trait = $this->makeCallbackTraitEnum($members);
        $trait::initialize(function() use($members) { return $members; });
        $extends = $this->makeCallbackExtendsEnum($members);
        $extends::initialize(function() use($members) { return $members; });

        $expected = ['FIRST' => 1, 'SECOND' => 2];
        $this->assertSame($expected, $trait::getMembersAndValues());
        $this->assertSame($expected, $extends::getMembersAndValues());
    }

    public function testExceptionNoInitialize(): void
    {
        $members = ['FIRST' => 1, 'SECOND' => 2];
        $class = $this->makeCallbackTraitEnum($members);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum '.$class.' requires static property $callback to be a valid callable returning members and values mapping.');
        $class::FIRST();
    }

    public function testExceptionAlreadyInitialized(): void
    {
        $members = ['FIRST' => 1, 'SECOND' => 2];
        $class = $this->makeCallbackTraitEnum($members);
        $class::initialize(function() use($members) { return $members; });

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum '.$class.' callback was already initialized.');
        $class::initialize(function() use($members) { return $members; });
    }

    public function testImpossibleExceptionCallbackNotCallable(): void
    {
        $class = $this->computeUniqueClassName('CallbackTrait');
        eval('final class '.$class.' implements \JsonSerializable { use '.CallbackEnumTrait::class.'; }');

        $ref = new \ReflectionClass($class);
        $callbacks = $ref->getProperty('callbacks');
        $callbacks->setAccessible(true);
        $callbacks->setValue($class, [$class => 'invalid']);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum '.$class.' requires static property $callback to be a valid callable returning members and values mapping.');
        $class::FIRST();
    }
}
