<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests;

use Thunder\Platenum\Enum\AbstractConstantsEnum;
use Thunder\Platenum\Enum\AbstractStaticEnum;
use Thunder\Platenum\Enum\EnumTrait;
use Thunder\Platenum\Exception\PlatenumException;
use Thunder\Platenum\Tests\Fake\FakeEnum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class EnumTest extends AbstractTestCase
{
    /* --- CREATE --- */

    public function testCreateFromMember(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $first = $enum::fromMember('FIRST');
        $second = $enum::fromMember('SECOND');

        $this->assertSame('FIRST', $first->getMember());
        $this->assertSame('SECOND', $second->getMember());
    }

    public function testCreateFromValue(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $first = $enum::fromValue(1);
        $second = $enum::fromValue(2);

        $this->assertSame('FIRST', $first->getMember());
        $this->assertSame('SECOND', $second->getMember());
        $this->assertSame(1, $first->getValue());
        $this->assertSame(2, $second->getValue());
    }

    public function testCreateFromConstant(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $first = $enum::FIRST();
        $second = $enum::SECOND();

        $this->assertSame('FIRST', $first->getMember());
        $this->assertSame('SECOND', $second->getMember());
        $this->assertSame(1, $first->getValue());
        $this->assertSame(2, $second->getValue());
    }

    public function testCreateFromEnum(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $first = $enum::fromMember('FIRST');
        $otherFirst = $enum::fromEnum($first);

        $this->assertSame('FIRST', $otherFirst->getMember());
        $this->assertSame(1, $otherFirst->getValue());
    }

    public function testExceptionNonScalarValue(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` value must be a scalar, `array` given.');
        $enum::fromValue([]);
    }

    public function testExceptionInvalidMemberConstant(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not contain member `THIRD` among `FIRST,SECOND`.');
        $enum::THIRD();
    }

    public function testExceptionInvalidMember(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not contain member `THIRD` among `FIRST,SECOND`.');
        $enum::fromMember('THIRD');
    }

    public function testExceptionInvalidValue(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not contain any member with value `42`.');
        $enum::fromValue(42);
    }

    public function testExceptionNonEmptyConstantArguments(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 'first', 'SECOND' => 'second']);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` constant methods must not have any arguments.');
        $enum::SECOND('invalid');
    }

    public function testExceptionCreateFromEnumDifferentClass(): void
    {
        $enumA = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $enumB = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Attempting to recreate enum '.$enumA.' from instance of '.$enumB.'.');
        $enumA::fromEnum($enumB::FIRST());
    }

    public function testExceptionCreateFromInstanceDifferentClass(): void
    {
        $enumA = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $enumB = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $b = $enumB::FIRST();
        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Attempting to recreate enum '.$enumA.' from instance of '.$enumB.'.');
        $enumA::FIRST()->fromInstance($b);
    }

    public function testExceptionMissingResolveMethod(): void
    {
        /** @var FakeEnum $enum */
        $enum = $this->computeUniqueClassName('ExtendsExtends');
        eval('class '.$enum.' { use Thunder\Platenum\Enum\EnumTrait; }');

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not implement resolve() method.');
        $enum::FIRST();
    }

    /* --- GENERIC --- */

    public function testExtendedExtendedEnum(): void
    {
        /** @var FakeEnum $classA */
        $classA = $this->computeUniqueClassName('X');
        /** @var FakeEnum $classB */
        $classB = $this->computeUniqueClassName('X');
        eval('class '.$classA.' extends '.AbstractConstantsEnum::class.' {
            protected const A = 1;
            protected const B = 2;
        }

        final class '.$classB.' extends '.$classA.' {}');

        $this->assertSame('B', $classA::B()->getMember());
        $this->assertSame('B', $classB::B()->getMember());
    }

    /* --- COMPARE --- */

    public function testSameEnumEquality(): void
    {
        $enumA = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $enumB = $this->makeRawEnum(['FIRST' => 'first', 'SECOND' => 'second']);

        $this->assertTrue($enumA::FIRST()->equals($enumA::FIRST()), 'constant equals constant');
        $this->assertSame($enumA::SECOND(), $enumA::SECOND(), 'constant === constant');
        $this->assertEquals($enumA::SECOND(), $enumA::SECOND(), 'constant == constant');
        $this->assertSame($enumA::fromMember('FIRST'), $enumA::fromMember('FIRST'), 'key === key');
        $this->assertSame($enumA::fromMember('FIRST'), $enumA::FIRST(), 'key === constant');

        $this->assertTrue($enumB::fromValue('first')->equals($enumB::FIRST()), 'value equals constant');
        $this->assertTrue($enumB::fromValue('first')->equals($enumB::fromValue('first')), 'value equals value');
        $this->assertTrue($enumB::FIRST()->equals($enumB::fromValue('first')), 'constant equals value');
        $this->assertSame($enumB::FIRST(), $enumB::fromValue('first'), 'constant === value');
        $this->assertSame($enumB::fromValue('second'), $enumB::SECOND(), 'value === constant');
    }

    public function testSameEnumInequality(): void
    {
        $enumA = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $enumB = $this->makeRawEnum(['FIRST' => 'first', 'SECOND' => 'second']);

        $this->assertFalse($enumA::FIRST()->equals($enumA::SECOND()), 'constant !equals constant');
        $this->assertNotSame($enumA::FIRST(), $enumA::SECOND(), 'constant !== constant');

        $this->assertFalse($enumB::fromValue('first')->equals($enumB::SECOND()), 'value !equals constant');
        $this->assertFalse($enumB::fromValue('first')->equals($enumB::fromValue('second')), 'value !equals value');
        $this->assertFalse($enumB::FIRST()->equals($enumB::fromValue('second')), 'constant !equals value');
        $this->assertNotSame($enumB::FIRST(), $enumB::fromValue('second'), 'constant !== value');
        $this->assertNotSame($enumB::fromValue('first'), $enumB::SECOND(), 'value !== constant');
    }

    public function testDifferentEnumsInequality(): void
    {
        $enumA = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $enumB = $this->makeRawEnum(['FIRST' => 'first', 'SECOND' => 'second']);

        $this->assertNotSame($enumA::FIRST(), $enumB::FIRST());
        $this->assertNotSame($enumA::SECOND(), $enumB::SECOND());
    }

    /* --- LOGIC --- */

    public function testExceptionNoMembers(): void
    {
        $enum = $this->makeRawEnum([]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not contain any members.');
        $enum::memberExists('WHICH_IT_DOES_NOT');
    }

    public function testExceptionNonUniqueMemberValues(): void
    {
        $enum = $this->makeRawEnum(['X1' => 1, 'X2' => 1]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` members values are not unique.');
        $enum::X1();
    }

    /* --- CHECK --- */

    public function testHasMember(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->assertTrue($enum::SECOND()->hasMember('SECOND'));
        $this->assertFalse($enum::SECOND()->hasMember('FIRST'));
    }

    public function testHasValue(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->assertTrue($enum::SECOND()->hasValue(2));
        $this->assertFalse($enum::SECOND()->hasValue(1));
    }

    public function testMemberExists(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->assertTrue($enum::memberExists('FIRST'));
        $this->assertFalse($enum::memberExists('THIRD'));
    }

    public function testValueExists(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->assertTrue($enum::valueExists(1));
        $this->assertFalse($enum::valueExists(3));
    }

    /* --- CONVERT --- */

    public function testGetMember(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->assertSame('FIRST', $enum::FIRST()->getMember());
        $this->assertSame('SECOND', $enum::SECOND()->getMember());
    }

    public function testGetValue(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->assertSame(1, $enum::FIRST()->getValue());
        $this->assertSame(2, $enum::SECOND()->getValue());
    }

    public function testMemberToValue(): void
    {
        $intEnum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $stringEnum = $this->makeRawEnum(['FIRST' => 'first', 'SECOND' => 'second']);

        $this->assertSame(1, $intEnum::memberToValue('FIRST'));
        $this->assertSame('first', $stringEnum::memberToValue('FIRST'));
    }

    public function testValueToMember(): void
    {
        $intEnum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $stringEnum = $this->makeRawEnum(['FIRST' => 'first', 'SECOND' => 'second']);

        $this->assertSame('FIRST', $intEnum::valueToMember(1));
        $this->assertSame('FIRST', $stringEnum::valueToMember('first'));
    }

    public function testJsonEncode(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->assertSame('2', json_encode($enum::SECOND()));
    }

    public function testCastToString(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->assertSame('2', (string)$enum::SECOND());
    }

    public function testExceptionInvalidMemberToValue(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not contain member `INVALID` among `FIRST,SECOND`.');
        $enum::memberToValue('INVALID');
    }

    public function testExceptionInvalidValueToMember(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not contain any member with value `invalid`.');
        $enum::valueToMember('invalid');
    }

    /* --- LIST --- */

    public function testListMembers(): void
    {
        $intEnum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $stringEnum = $this->makeRawEnum(['FIRST' => 'first', 'SECOND' => 'second']);

        $this->assertSame(['FIRST', 'SECOND'], $intEnum::getMembers());
        $this->assertSame(['FIRST', 'SECOND'], $stringEnum::getMembers());
    }

    public function testListValues(): void
    {
        $intEnum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $stringEnum = $this->makeRawEnum(['FIRST' => 'first', 'SECOND' => 'second']);

        $this->assertSame([1, 2], $intEnum::getValues());
        $this->assertSame(['first', 'second'], $stringEnum::getValues());
    }

    public function testListMembersAndValues(): void
    {
        $intEnum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $stringEnum = $this->makeRawEnum(['FIRST' => 'first', 'SECOND' => 'second']);

        $this->assertSame(['FIRST' => 1, 'SECOND' => 2], $intEnum::getMembersAndValues());
        $this->assertSame(['FIRST' => 'first', 'SECOND' => 'second'], $stringEnum::getMembersAndValues());
    }

    /* --- RUNTIME --- */

    public function testSerialize(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);
        $className = $this->computeUniqueClassName('X');
        eval('final class '.$className.' {
            /** @var EnumTrait; */
            private $enum;
            public function __construct($enum) { $this->enum = $enum; }
            public function getEnum() { return $this->enum; }
            public function __wakeup() { $this->enum->fromInstance($this->enum); }
        };');

        $original = $enum::FIRST();
        $unserialized = unserialize(serialize(new $className($original)))->getEnum();

        $this->assertSame($original, $unserialized);
    }

    public function testExceptionMagicClone(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not allow magic `__clone` method.');
        $var = clone $enum::FIRST();
    }

    public function testExceptionMagicInvoke(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not allow magic `__invoke` method.');
        $enum::FIRST()();
    }

    public function testExceptionMagicCall(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not allow magic `__call` method.');
        $enum::FIRST()->invalidMethod();
    }

    public function testExceptionMagicGet(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not allow magic `__get` method.');
        $enum::FIRST()->invalidProperty;
    }

    public function testExceptionMagicSet(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not allow magic `__set` method.');
        $enum::FIRST()->invalidProperty = 'value';
    }

    public function testExceptionMagicIsset(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not allow magic `__isset` method.');
        $is = isset($enum::FIRST()->invalidProperty);
    }

    public function testExceptionMagicUnset(): void
    {
        $enum = $this->makeRawEnum(['FIRST' => 1, 'SECOND' => 2]);

        $this->expectException(PlatenumException::class);
        $this->expectExceptionMessage('Enum `'.$enum.'` does not allow magic `__unset` method.');
        unset($enum::FIRST()->invalidProperty);
    }

    /* --- CUSTOM EXCEPTION --- */

    /** @dataProvider provideCustomExceptions */
    public function testTraitCustomException(string $type, callable $handler): void
    {
        $this->runCustomExceptionTest('trait', $type, $handler);
    }

    /** @dataProvider provideCustomExceptions */
    public function testExtendsCustomException(string $type, callable $handler): void
    {
        $this->runCustomExceptionTest('extends', $type, $handler);
    }

    private function runCustomExceptionTest(string $const, string $type, callable $handler): void
    {
        $members = ['FIRST' => 1];
        $methodMap = [
            'invalidMember' => 'throwInvalidMemberException(string $member)',
            'invalidValue' => 'throwInvalidValueException($value)',
        ];
        if(false === isset($methodMap[$type])) {
            throw new \LogicException(sprintf('Unrecognized override type `%s`.', $type));
        }

        $exceptionClass = $this->computeUniqueClassName('EnumException');
        eval('final class '.$exceptionClass.' extends \Exception {}');

        $class = $this->computeUniqueClassName('EnumCustomException');
        $resolve = 'private static function resolve(): array { return '.var_export($members, true).'; }';
        $mapping = 'protected static $mapping = '.var_export($members, true).';';
        $override = 'protected static function '.$methodMap[$type].': void { throw new '.$exceptionClass.'(); }';
        switch($const) {
            case 'extends': { $code = 'final class '.$class.' extends '.AbstractStaticEnum::class.' {  '.$mapping.$override.' }'; break; }
            case 'trait':   { $code = 'final class '.$class.' { use '.EnumTrait::class.'; '.$resolve.$override.' }'; break; }
            default: { throw new \LogicException(sprintf('Invalid extension type `%s`.', $const)); }
        }
        eval($code);

        $this->expectException($exceptionClass);
        $handler($class);
    }

    public function provideCustomExceptions(): array
    {
        return [
            ['invalidMember', function(string $class) { return $class::INVALID(); }],
            ['invalidMember', function(string $class) { return $class::fromMember('INVALID'); }],
            ['invalidValue', function(string $class) { return $class::fromValue('invalid'); }],
            ['invalidMember', function(string $class) { return $class::memberToValue('INVALID'); }],
            ['invalidValue', function(string $class) { return $class::valueToMember('invalid'); }],
        ];
    }

    /** @dataProvider provideCustomExceptions */
    public function testTraitCustomExceptionEmptyMethod(string $type, callable $handler): void
    {
        $this->runCustomExceptionEmptyMethodTest('trait', $type, $handler);
    }

    /** @dataProvider provideCustomExceptions */
    public function testExtendsCustomExceptionEmptyMethod(string $type, callable $handler): void
    {
        $this->runCustomExceptionEmptyMethodTest('extends', $type, $handler);
    }

    private function runCustomExceptionEmptyMethodTest(string $const, string $type, callable $handler): void
    {
        $members = ['FIRST' => 1];
        $methodMap = [
            'invalidMember' => 'throwInvalidMemberException(string $member)',
            'invalidValue' => 'throwInvalidValueException($value)',
        ];
        if(false === isset($methodMap[$type])) {
            throw new \LogicException(sprintf('Unrecognized override type `%s`.', $type));
        }

        $class = $this->computeUniqueClassName('EnumCustomException');
        $resolve = 'private static function resolve(): array { return '.var_export($members, true).'; }';
        $mapping = 'protected static $mapping = '.var_export($members, true).';';
        $override = 'protected static function '.$methodMap[$type].': void {}';
        switch($const) {
            case 'extends': { $code = 'final class '.$class.' extends '.AbstractStaticEnum::class.' {  '.$mapping.$override.' }'; break; }
            case 'trait':   { $code = 'final class '.$class.' { use '.EnumTrait::class.'; '.$resolve.$override.' }'; break; }
            default: { throw new \LogicException(sprintf('Invalid extension type `%s`.', $const)); }
        }
        eval($code);

        $this->expectException(PlatenumException::class);
        $handler($class);
    }
}
