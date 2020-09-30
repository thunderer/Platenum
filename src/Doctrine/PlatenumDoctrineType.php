<?php
declare(strict_types=1);
namespace Thunder\Platenum\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Thunder\Platenum\Enum\EnumTrait;

/** @psalm-suppress PropertyNotSetInConstructor */
final class PlatenumDoctrineType extends Type
{
    /** @var class-string */
    private $platenumClass;
    /** @var string */
    private $platenumAlias;
    /** @var callable */
    private $platenumCallback;
    /** @var callable(array,AbstractPlatform):string */
    private $platenumSql;

    /**
     * @param string $alias
     * @param class-string $class
     */
    public static function registerInteger(string $alias, string $class): void
    {
        /** @psalm-suppress MissingClosureParamType */
        $toInteger = function($value): int {
            return (int)$value;
        };
        $sql = function(array $declaration, AbstractPlatform $platform): string {
            return $platform->getIntegerTypeDeclarationSQL([]);
        };

        static::registerCallback($alias, $class, $toInteger, $sql);
    }

    /**
     * @param string $alias
     * @param class-string $class
     */
    public static function registerString(string $alias, string $class): void
    {
        /** @psalm-suppress MissingClosureParamType */
        $toString = function($value): string {
            return (string)$value;
        };
        $sql = function(array $declaration, AbstractPlatform $platform): string {
            return $platform->getVarcharTypeDeclarationSQL([]);
        };

        static::registerCallback($alias, $class, $toString, $sql);
    }

    /**
     * @param string $alias
     * @param class-string $class
     * @param callable $callback
     * @param callable(array<mixed>,AbstractPlatform):string $sql
     */
    private static function registerCallback(string $alias, string $class, callable $callback, callable $sql): void
    {
        if(static::hasType($alias)) {
            throw new \LogicException(sprintf('Alias `%s` was already registered in PlatenumDoctrineType.', $class));
        }
        if(false === in_array(EnumTrait::class, static::allTraitsOf($class))) {
            throw new \LogicException(sprintf('PlatenumDoctrineType allows only Platenum enumerations, `%s` given.', $class));
        }

        static::addType($alias, static::class);

        /** @var static $type */
        $type = static::getType($alias);
        $type->platenumAlias = $alias;
        $type->platenumClass = $class;
        $type->platenumCallback = $callback;
        $type->platenumSql = $sql;
    }

    /**
     * @param class-string $class
     * @psalm-return list<string>
     */
    private static function allTraitsOf(string $class): array
    {
        $traits = [];

        do {
            $traits = array_merge(class_uses($class, true), $traits);
        } while($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, true), $traits);
        }

        return array_values(array_unique($traits));
    }

    public function getName(): string
    {
        return $this->platenumAlias;
    }

    /**
     * @psalm-suppress ParamNameMismatch
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return ($this->platenumSql)($column, $platform);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        /** @psalm-suppress MixedMethodCall */
        return ($this->platenumCallback)($value->getValue());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        /** @psalm-suppress MixedMethodCall */
        return ($this->platenumClass)::fromValue(($this->platenumCallback)($value));
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
