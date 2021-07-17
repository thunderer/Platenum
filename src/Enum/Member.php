<?php
declare(strict_types=1);
namespace Thunder\Platenum\Enum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 * @psalm-immutable
 */
#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
final class Member
{
    /** @var string */
    public $member;
    /** @var int|string */
    public $value;

    /**
     * @param string $member
     * @param int|string $value
     */
    public function __construct($member, $value)
    {
        $this->member = $member;
        $this->value = $value;
    }
}
