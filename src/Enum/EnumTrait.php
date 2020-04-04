<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

use Thunder\Platenum\Exception\PlatenumException;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
trait EnumTrait
{
    /** @var string */
    private $member;
    /** @var int|string */
    private $value;

    /** @var array */
    protected static $members = [];
    /** @var static[] */
    protected static $instances = [];

    final private function __construct(string $member, $value)
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

        return static::$instances[$class][$member] = new static($member, static::$members[$class][$member]);
    }

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

        return static::fromMember($member);
    }

    final public static function fromEnum($enum): self
    {
        if(false === $enum instanceof static) {
            throw PlatenumException::fromMismatchedClass(static::class, \get_class($enum));
        }

        return static::fromValue($enum->value);
    }

    final public function fromInstance(&$enum): void
    {
        if(false === $enum instanceof static) {
            throw PlatenumException::fromMismatchedClass(static::class, \get_class($enum));
        }

        $enum = static::fromEnum($enum);
    }

    /* --- EXCEPTIONS --- */

    protected static function throwInvalidMemberException(string $member): void
    {
        static::throwDefaultInvalidMemberException($member);
    }

    private static function throwDefaultInvalidMemberException(string $member): void
    {
        throw PlatenumException::fromInvalidMember(static::class, $member, static::$members[static::class]);
    }

    protected static function throwInvalidValueException($value): void
    {
        static::throwDefaultInvalidValueException($value);
    }

    private static function throwDefaultInvalidValueException($value): void
    {
        throw PlatenumException::fromInvalidValue(static::class, $value);
    }

    /* --- COMPARE --- */

    final public function equals($other): bool
    {
        return $other instanceof $this && $this->value === $other->value;
    }

    /* --- TRANSFORM --- */

    final public function getMember(): string
    {
        return $this->member;
    }

    final public function getValue()
    {
        return $this->value;
    }

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

    final public static function valueExists($value): bool
    {
        static::resolveMembers();

        return \in_array($value, static::$members[static::class], true);
    }

    final public function hasMember(string $members): bool
    {
        return $members === $this->member;
    }

    final public function hasValue($value): bool
    {
        return $value === $this->value;
    }

    /* --- INFO --- */

    final public static function memberToValue(string $member)
    {
        static::resolveMembers();

        $class = static::class;
        if(false === static::memberExists($member)) {
            static::throwInvalidMemberException($member);
            static::throwDefaultInvalidMemberException($member);
        }

        return static::$members[$class][$member];
    }

    final public static function valueToMember($value)
    {
        static::resolveMembers();

        $class = static::class;
        if(false === static::valueExists($value)) {
            static::throwInvalidValueException($value);
            static::throwDefaultInvalidValueException($value);
        }

        return array_search($value, static::$members[$class], true);
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

    final private static function resolveMembers(): void
    {
        $class = static::class;
        if(isset(static::$members[$class])) {
            return;
        }

        // reflection instead of method_exists because of PHP 7.4 bug #78632
        // @see https://bugs.php.net/bug.php?id=78632
        if(false === (new \ReflectionClass($class))->hasMethod('resolve')) {
            throw PlatenumException::fromMissingResolve($class);
        }
        $members = static::resolve();
        if(empty($members)) {
            throw PlatenumException::fromEmptyMembers($class);
        }
        if(\count($members) !== \count(\array_unique($members))) {
            throw PlatenumException::fromNonUniqueMembers($class);
        }

        static::$members[$class] = $members;
    }

    /* --- MAGIC --- */

    final public function __clone()
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __call($name, $arguments)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __invoke()
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __isset($name)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __unset($name)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __set($name, $value)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }

    final public function __get($name)
    {
        throw PlatenumException::fromMagicMethod(static::class, __FUNCTION__);
    }
}
