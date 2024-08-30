<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

use Thunder\Platenum\Exception\PlatenumException;

/**
 * @template TMember
 * @template TValue
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
trait NativeEnumTrait
{
    /* --- CREATE --- */

    final public static function __callStatic(string $member, array $arguments)
    {
        $class = static::class;
        if($arguments) {
            throw PlatenumException::fromConstantArguments($class);
        }

        return static::fromMember($member);
    }

    final public static function fromMember(string $member): self
    {
        $found = false;
        foreach(static::cases() as $case) {
            if($case->name === $member) {
                $found = true;
                $value = $case->value;
            }
        }
        if(false === $found) {
            self::throwInvalidMemberException($member);
            self::throwDefaultInvalidMemberException($member);
        }

        /** @psalm-suppress UnsafeInstantiation,ArgumentTypeCoercion,PropertyTypeCoercion */
        return static::from($value);
    }

    /**
     * @param mixed $value
     * @return static
     */
    final public static function fromValue($value): self
    {
        $class = static::class;
        if(false === is_scalar($value)) {
            throw PlatenumException::fromIllegalValue($class, $value);
        }

        $found = false;
        foreach(static::cases() as $case) {
            if($case->value === $value) {
                $found = true;
            }
        }
        if(false === $found) {
            static::throwInvalidValueException($value);
            static::throwDefaultInvalidValueException($value);
        }

        /** @var string $member */
        return static::from($value);
    }

    /**
     * @param static $enum
     * @return static
     */
    final public static function fromEnum($enum): self
    {
        if(false === $enum instanceof static) {
            throw PlatenumException::fromMismatchedClass(static::class, \get_class($enum));
        }

        return static::fromValue($enum->value);
    }

    /**
     * @psalm-suppress ReferenceConstraintViolation
     * @param static $enum
     * @param-out AbstractConstantsEnum|AbstractDocblockEnum|AbstractStaticEnum|AbstractCallbackEnum|AbstractAttributeEnum $enum
     */
    final public function fromInstance(&$enum): void
    {
        $enum = static::fromEnum($enum);
    }

    /**
     * @psalm-suppress UnusedForeachValue
     * @return list<static>
     */
    final public static function getInstances(): array
    {
        return array_values(static::cases());
    }

    /* --- EXCEPTIONS --- */

    /** @psalm-suppress UnusedParam */
    private static function throwInvalidMemberException(string $member): void
    {
    }

    private static function throwDefaultInvalidMemberException(string $member): void
    {
        throw PlatenumException::fromInvalidMember(static::class, $member, array_flip(static::getMembers()));
    }

    /**
     * @param mixed $value
     * @psalm-suppress UnusedParam
     */
    private static function throwInvalidValueException($value): void
    {
    }

    /** @param mixed $value */
    private static function throwDefaultInvalidValueException($value): void
    {
        throw PlatenumException::fromInvalidValue(static::class, $value);
    }

    /* --- COMPARE --- */

    /** @param static $other */
    final public function equals($other): bool
    {
        return $other instanceof $this && $this->value === $other->value;
    }

    /* --- TRANSFORM --- */

    /** @return TMember */
    final public function getMember(): string
    {
        return $this->name;
    }

    /** @return TValue */
    final public function getValue()
    {
        return $this->value;
    }

    /** @psalm-suppress MissingReturnType */
    #[\ReturnTypeWillChange]
    final public function jsonSerialize()
    {
        return $this->getValue();
    }

    /* --- CHECK --- */

    final public static function memberExists(string $member): bool
    {
        foreach(static::cases() as $case) {
            if($case->name === $member) {
                return true;
            }
        }

        return false;
    }

    /** @param int|string $value */
    final public static function valueExists($value): bool
    {
        foreach(static::cases() as $case) {
            if($case->value === $value) {
                return true;
            }
        }

        return false;
    }

    /** @param list<string> $members */
    final public static function membersExist(array $members): bool
    {
        $list = [];
        foreach(static::cases() as $case) {
            $list[] = $case->name;
        }

        return [] !== array_intersect($list, $members);
    }

    /** @param list<int|string> $values */
    final public static function valuesExist(array $values): bool
    {
        $list = [];
        foreach(static::cases() as $case) {
            $list[] = $case->value;
        }

        return [] !== array_intersect($list, $values);
    }

    /** @param TMember $member */
    final public function hasMember(string $member): bool
    {
        return $member === $this->name;
    }

    /** @param TValue $value */
    final public function hasValue($value): bool
    {
        return $value === $this->value;
    }

    /** @param list<TMember> $members */
    final public function hasMemberIn(array $members): bool
    {
        return in_array($this->name, $members, true);
    }

    /** @param list<TValue> $values */
    final public function hasValueIn(array $values): bool
    {
        return in_array($this->value, $values, true);
    }

    /** @param list<static> $enums */
    final public function isIn(array $enums): bool
    {
        return in_array($this, $enums, true);
    }

    final public static function isMemberWarm(string $member): bool
    {
        return true;
    }

    /* --- INFO --- */

    /** @return int|string */
    final public static function memberToValue(string $member)
    {
        /** @var \BackedEnum $case */
        foreach(static::cases() as $case) {
            if($case->name === $member) {
                return $case->value;
            }
        }

        static::throwInvalidMemberException($member);
        static::throwDefaultInvalidMemberException($member);
    }

    /** @param int|string $value */
    final public static function valueToMember($value): string
    {
        /** @var \BackedEnum $case */
        foreach(static::cases() as $case) {
            if($case->value === $value) {
                return $case->name;
            }
        }

        static::throwInvalidValueException($value);
        static::throwDefaultInvalidValueException($value);
    }

    final public static function getMembers(): array
    {
        $list = [];
        /** @var \BackedEnum $case */
        foreach(static::cases() as $case) {
            $list[] = $case->name;
        }

        return $list;
    }

    final public static function getValues(): array
    {
        $list = [];
        /** @var \BackedEnum $case */
        foreach(static::cases() as $case) {
            $list[] = $case->value;
        }

        return $list;
    }

    final public static function getMembersAndValues(): array
    {
        $map = [];
        /** @var \BackedEnum $case */
        foreach(static::cases() as $case) {
            $map[$case->name] = $case->value;
        }

        return $map;
    }

    /* --- MAGIC --- */

    final public function __call(string $name, array $arguments)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __invoke()
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }
}
