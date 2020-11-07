<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @psalm-template T
 * @psalm-immutable
 */
abstract class AbstractCallbackEnum implements \JsonSerializable
{
    /** @use CallbackEnumTrait<T> */
    use CallbackEnumTrait;
}
