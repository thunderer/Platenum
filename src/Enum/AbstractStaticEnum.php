<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
abstract class AbstractStaticEnum implements \JsonSerializable
{
    use StaticEnumTrait;

    protected static $mapping = [];
}
