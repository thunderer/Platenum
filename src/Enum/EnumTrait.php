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
trait EnumTrait
{
    /** @var TMember */
    private $member;
    /** @var TValue */
    private $value;

    /** @var non-empty-array<string,non-empty-array<string,int|string>> */
    protected static $members = [];
    /** @var array<string,array<string,static>> */
    protected static $instances = [];


    /**
     * @param TMember $member
     * @param TValue $value
     */
    /* final */ private function __construct(string $member, $value)
    {
        $this->member = $member;
        $this->value = $value;
    }

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
        $class = static::class;
        if(isset(static::$instances[$class][$member])) {
            return static::$instances[$class][$member];
        }

        static::resolveMembers();
        if(false === array_key_exists($member, static::$members[$class])) {
            static::throwInvalidMemberException($member);
            static::throwDefaultInvalidMemberException($member);
        }

        /** @psalm-suppress UnsafeInstantiation,ArgumentTypeCoercion,PropertyTypeCoercion */
        return static::$instances[$class][$member] = new static($member, static::$members[$class][$member]);
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

        static::resolveMembers();
        $member = array_search($value, static::$members[$class], true);
        if(false === $member) {
            static::throwInvalidValueException($value);
            static::throwDefaultInvalidValueException($value);
        }

        /** @var string $member */
        return static::fromMember($member);
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
        static::resolveMembers();
        foreach(static::$members[static::class] as $member => $value) {
            static::fromMember($member);
        }

        return array_values(static::$instances[static::class]);
    }

    /* --- EXCEPTIONS --- */

    /** @psalm-suppress UnusedParam */
    private static function throwInvalidMemberException(string $member): void
    {
    }

    private static function throwDefaultInvalidMemberException(string $member): void
    {
        throw PlatenumException::fromInvalidMember(static::class, $member, static::$members[static::class]);
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
        return $this->member;
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

    final public function __toString(): string
    {
        return (string)$this->getValue();
    }

    /* --- CHECK --- */

    final public static function memberExists(string $member): bool
    {
        static::resolveMembers();

        return array_key_exists($member, static::$members[static::class]);
    }

    /** @param int|string $value */
    final public static function valueExists($value): bool
    {
        static::resolveMembers();

        return \in_array($value, static::$members[static::class], true);
    }

    /** @param list<string> $members */
    final public static function membersExist(array $members): bool
    {
        static::resolveMembers();

        return [] !== array_intersect(array_keys(static::$members[static::class]), $members);
    }

    /** @param list<int|string> $values */
    final public static function valuesExist(array $values): bool
    {
        static::resolveMembers();

        return [] !== array_intersect(static::$members[static::class], $values);
    }

    /** @param TMember $member */
    final public function hasMember(string $member): bool
    {
        return $member === $this->member;
    }

    /** @param TValue $value */
    final public function hasValue($value): bool
    {
        return $value === $this->value;
    }

    /** @param list<TMember> $members */
    final public function hasMemberIn(array $members): bool
    {
        return in_array($this->member, $members, true);
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
        return self::memberExists($member)
            && array_key_exists(static::class, static::$instances)
            && array_key_exists($member, static::$instances[static::class]);
    }

    /* --- INFO --- */

    /** @return int|string */
    final public static function memberToValue(string $member)
    {
        if(false === static::memberExists($member)) {
            static::throwInvalidMemberException($member);
            static::throwDefaultInvalidMemberException($member);
        }

        return static::$members[static::class][$member];
    }

    /** @param int|string $value */
    final public static function valueToMember($value): string
    {
        if(false === static::valueExists($value)) {
            static::throwInvalidValueException($value);
            static::throwDefaultInvalidValueException($value);
        }

        return (string)array_search($value, static::$members[static::class], true);
    }

    final public static function getMembers(): array
    {
        static::resolveMembers();

        return array_keys(static::$members[static::class]);
    }

    final public static function getValues(): array
    {
        static::resolveMembers();

        return array_values(static::$members[static::class]);
    }

    final public static function getMembersAndValues(): array
    {
        static::resolveMembers();

        return static::$members[static::class];
    }

    /* --- SOURCE --- */

    private static function resolveMembers(): void
    {
        $class = static::class;
        if(isset(static::$members[$class])) {
            return;
        }

        $throwMissingResolve = function(string $class): void {
            throw PlatenumException::fromMissingResolve($class);
        };
        // reflection instead of method_exists because of PHP 7.4 bug #78632
        // @see https://bugs.php.net/bug.php?id=78632
        $hasResolve = (new \ReflectionClass($class))->hasMethod('resolve');
        /** @var array<string,int|string> $members */
        $members = $hasResolve ? static::resolve() : $throwMissingResolve($class);
        if(empty($members)) {
            throw PlatenumException::fromEmptyMembers($class);
        }
        if(\count($members) !== \count(\array_unique($members))) {
            throw PlatenumException::fromNonUniqueMembers($class);
        }
        if(['string'] !== \array_unique(\array_map('gettype', array_keys($members)))) {
            throw PlatenumException::fromNonStringMembers($class);
        }
        if(1 !== \count(\array_unique(\array_map('gettype', $members)))) {
            throw PlatenumException::fromNonUniformMemberValues($class, $members);
        }

        static::$members[$class] = $members;
    }

    /* --- MAGIC --- */

    final public function __clone()
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __call(string $name, array $arguments)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __invoke()
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    /** @param mixed $name */
    final public function __isset($name)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    /** @param mixed $name */
    final public function __unset($name)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    /** @param mixed $value */
    final public function __set(string $name, $value)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __get(string $name)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }
}
