<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @template TMember
 * @template TValue
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
trait AttributeEnumTrait
{
    /** @use EnumTrait<TMember,TValue> */
    use EnumTrait;

    /** @psalm-suppress UndefinedDocblockClass, UndefinedMethod because there is no ReflectionAttribute on PHP <8.0 */
    private static function resolve(): array
    {
        /** @var \ReflectionAttribute[] $attributes */
        $attributes = (new \ReflectionClass(static::class))->getAttributes(Member::class);
        $members = [];
        foreach($attributes as $attribute) {
            /** @var Member $member */
            $member = $attribute->newInstance();
            $members[$member->member] = $member->value;
        }

        return $members;
    }
}
