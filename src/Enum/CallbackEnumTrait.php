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
trait CallbackEnumTrait
{
    /** @use EnumTrait<TMember,TValue> */
    use EnumTrait;

    /** @var non-empty-array<class-string,callable():array<string,int|string>> */
    protected static $callbacks = [];

    /** @param callable():array<string,int|string> $callback */
    final public static function initialize(callable $callback): void
    {
        if(array_key_exists(static::class, static::$callbacks)) {
            throw PlatenumException::fromAlreadyInitializedCallback(static::class);
        }

        static::$callbacks[static::class] = $callback;
    }

    private static function resolve(): array
    {
        $class = static::class;
        if(false === (array_key_exists($class, static::$callbacks) && is_callable(static::$callbacks[$class]))) {
            throw PlatenumException::fromInvalidCallback($class);
        }

        return (static::$callbacks[$class])();
    }
}
