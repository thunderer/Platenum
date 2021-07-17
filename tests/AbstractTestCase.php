<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests;

use PHPUnit\Framework\TestCase;
use Thunder\Platenum\Enum\AbstractAttributeEnum;
use Thunder\Platenum\Enum\AbstractCallbackEnum;
use Thunder\Platenum\Enum\AbstractConstantsEnum;
use Thunder\Platenum\Enum\AbstractDocblockEnum;
use Thunder\Platenum\Enum\AbstractStaticEnum;
use Thunder\Platenum\Enum\AttributeEnumTrait;
use Thunder\Platenum\Enum\CallbackEnumTrait;
use Thunder\Platenum\Enum\ConstantsEnumTrait;
use Thunder\Platenum\Enum\DocblockEnumTrait;
use Thunder\Platenum\Enum\EnumTrait;
use Thunder\Platenum\Enum\Member;
use Thunder\Platenum\Enum\StaticEnumTrait;
use Thunder\Platenum\Tests\Fake\FakeEnum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
abstract class AbstractTestCase extends TestCase
{
    /** @return FakeEnum */
    protected function makeRawEnum(array $members): string
    {
        $entries = [];
        foreach($members as $member => $value) {
            $entries[] = sprintf('%s => %s', '\''.$member.'\'', is_string($value) ? '\''.$value.'\'' : $value);
        }

        $class = $this->computeUniqueClassName('RawEnum');
        eval('final class '.$class.' implements \JsonSerializable {
            use '.EnumTrait::class.';
            private static function resolve(): array { return ['.implode(', ', $entries).']; }
        }');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeDocblockTraitEnum(array $members): string
    {
        $entries = [];
        foreach($members as $member => $value) {
            $entries[] = sprintf(' * @method static self %s()', $member);
        }

        $class = $this->computeUniqueClassName('DocblockTrait');
        eval('/**'."\n".implode("\n", $entries)."\n".'*/ final class '.$class.' { use '.DocblockEnumTrait::class.'; }');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeDocblockExtendsEnum(array $members): string
    {
        $entries = [];
        foreach($members as $member => $value) {
            $entries[] = sprintf(' * @method static self %s()', $member);
        }

        $class = $this->computeUniqueClassName('DocblockExtends');
        eval('/**'."\n".implode("\n", $entries)."\n".'*/ final class '.$class.' extends '.AbstractDocblockEnum::class.' {}');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeConstantsTraitEnum(array $members): string
    {
        $entries = [];
        foreach($members as $member => $value) {
            $entries[] = sprintf('private const %s = %s;', $member, is_string($value) ? '\''.$value.'\'' : $value);
        }

        $class = $this->computeUniqueClassName('ConstantsTrait');
        eval('final class '.$class.' implements \JsonSerializable { '.implode("\n", $entries).' use '.ConstantsEnumTrait::class.'; }');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeConstantsExtendsEnum(array $members): string
    {
        $entries = [];
        foreach($members as $member => $value) {
            $entries[] = sprintf('private const %s = %s;', $member, is_string($value) ? '\''.$value.'\'' : $value);
        }

        $class = $this->computeUniqueClassName('ConstantsExtends');
        eval('final class '.$class.' extends '.AbstractConstantsEnum::class.' { '.implode("\n", $entries).' }');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeStaticTraitEnum(array $members): string
    {
        $entries = [];
        foreach($members as $member => $value) {
            $entries[] = sprintf('%s => %s', '\''.$member.'\'', is_string($value) ? '\''.$value.'\'' : $value);
        }

        $class = $this->computeUniqueClassName('StaticTrait');
        eval('final class '.$class.' implements \JsonSerializable { private static $mapping = ['.implode(', ', $entries).']; use '.StaticEnumTrait::class.'; }');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeStaticExtendsEnum(array $members): string
    {
        $entries = [];
        foreach($members as $member => $value) {
            $entries[] = sprintf('%s => %s', '\''.$member.'\'', is_string($value) ? '\''.$value.'\'' : $value);
        }

        $class = $this->computeUniqueClassName('StaticExtends');
        eval('final class '.$class.' extends '.AbstractStaticEnum::class.' { protected static $mapping = ['.implode(', ', $entries).']; }');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeCallbackTraitEnum(array $members): string
    {
        $class = $this->computeUniqueClassName('CallbackTrait');
        eval('final class '.$class.' implements \JsonSerializable { use '.CallbackEnumTrait::class.'; }');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeCallbackExtendsEnum(array $members): string
    {
        $class = $this->computeUniqueClassName('CallbackExtends');
        eval('final class '.$class.' extends '.AbstractCallbackEnum::class.' { }');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeAttributeTraitEnum(array $members): string
    {
        $attributes = [];
        foreach($members as $member => $value) {
            $attributes[] = '#['.Member::class.'(\''.$member.'\', '.$value.')]';
        }

        $class = $this->computeUniqueClassName('AttributeTrait');
        eval(implode("\n", $attributes)."\n".'final class '.$class.' implements \JsonSerializable { use '.AttributeEnumTrait::class.'; }');

        return $class;
    }

    /** @return FakeEnum */
    protected function makeAttributeExtendsEnum(array $members): string
    {
        $attributes = [];
        foreach($members as $member => $value) {
            $attributes[] = '#['.Member::class.'(\''.$member.'\', '.$value.')]';
        }

        $class = $this->computeUniqueClassName('AttributeExtends');
        eval(implode("\n", $attributes)."\n".'final class '.$class.' extends '.AbstractAttributeEnum::class.' {}');

        return $class;
    }

    protected function computeUniqueClassName(string $prefix): string
    {
        while(true === class_exists($class = $prefix.random_int(1, 1000000))) {
            continue;
        }

        return $class;
    }
}
