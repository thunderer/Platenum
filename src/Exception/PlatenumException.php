<?php
declare(strict_types=1);
namespace Thunder\Platenum\Exception;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class PlatenumException extends \LogicException
{
    /* --- OVERRIDE --- */

    public static function fromInvalidMember(string $fqcn, string $member, array $members): self
    {
        return new self(sprintf('Enum `%s` does not contain member `%s` among `%s`.', $fqcn, $member, implode(',', array_keys($members))));
    }

    /** @param mixed $value */
    public static function fromInvalidValue(string $fqcn, $value): self
    {
        return new self(sprintf('Enum `%s` does not contain any member with value `%s`.', $fqcn, is_scalar($value) ? strval($value) : gettype($value)));
    }

    /* --- GENERIC --- */

    /** @param mixed $value */
    public static function fromIllegalValue(string $fqcn, $value): self
    {
        return new self(sprintf('Enum `%s` value must be a scalar, `%s` given.', $fqcn, gettype($value)));
    }

    public static function fromMismatchedClass(string $fqcn, string $other): self
    {
        return new self(sprintf('Attempting to recreate enum %s from instance of %s.', $fqcn, $other));
    }

    public static function fromConstantArguments(string $fqcn): self
    {
        return new self(sprintf('Enum `%s` constant methods must not have any arguments.', $fqcn));
    }

    public static function fromNonUniqueMembers(string $fqcn): self
    {
        return new self(sprintf('Enum `%s` members values are not unique.', $fqcn));
    }

    public static function fromMissingResolve(string $fqcn): self
    {
        return new self(sprintf('Enum `%s` does not implement resolve() method.', $fqcn));
    }

    public static function fromEmptyMembers(string $fqcn): self
    {
        return new self(sprintf('Enum `%s` does not contain any members.', $fqcn));
    }

    public static function fromMagicMethod(string $fqcn, string $method): self
    {
        return new self(sprintf('Enum `%s` does not allow magic `%s` method.', $fqcn, $method));
    }

    public static function fromNonStringMembers(string $fqcn): self
    {
        return new self(sprintf('Enum `%s` requires all members to be strings.', $fqcn));
    }

    public static function fromNonUniformMemberValues(string $fqcn, array $members): self
    {
        /** @psalm-suppress MissingClosureParamType */
        $callback = function($value): string { return gettype($value); };
        $values = array_unique(array_map($callback, $members));

        return new self(sprintf('Enum `%s` member values must be of the same type, `%s` given.', $fqcn, implode(',', $values)));
    }

    /* --- DOCBLOCK --- */

    public static function fromMissingDocblock(string $fqcn): self
    {
        return new self(sprintf('Enum %s does not have a docblock with member definitions.', $fqcn));
    }

    public static function fromEmptyDocblock(string $fqcn): self
    {
        return new self(sprintf('Enum %s contains docblock without any member definitions.', $fqcn));
    }

    public static function fromMalformedDocblock(string $fqcn): self
    {
        return new self(sprintf('Enum %s contains malformed docblock member definitions.', $fqcn));
    }

    /* --- STATIC --- */

    public static function fromMissingMappingProperty(string $fqcn): self
    {
        return new self(sprintf('Enum %s requires static property $mapping with members definitions.', $fqcn));
    }

    /* --- CALLBACK --- */

    public static function fromInvalidCallback(string $fqcn): self
    {
        return new self(sprintf('Enum %s requires static property $callback to be a valid callable returning members and values mapping.', $fqcn));
    }

    public static function fromAlreadyInitializedCallback(string $fqcn): self
    {
        return new self(sprintf('Enum %s callback was already initialized.', $fqcn));
    }
}
