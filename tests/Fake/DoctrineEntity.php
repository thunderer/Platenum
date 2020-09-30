<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests\Fake;

use Doctrine\ORM\Mapping\ClassMetadata;
use Thunder\Platenum\Enum\ConstantsEnumTrait;

final class DoctrineEntity
{
    private $id;
    private $intValue;
    private $stringValue;
    private $nullableValue;

    public function __construct(
        int $id,
        DoctrineIntEnum $int,
        DoctrineStringEnum $string,
        ?DoctrineStringEnum $nullableString = null
    ) {
        $this->id = $id;
        $this->intValue = $int;
        $this->stringValue = $string;
        $this->nullableValue = $nullableString;
    }

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable(['name' => 'doctrine_entity']);

        $metadata->mapField(['id' => true, 'fieldName' => 'id', 'type' => 'integer']);
        $metadata->mapField(['fieldName' => 'intValue', 'columnName' => 'int_value', 'type' => 'intEnum']);
        $metadata->mapField(['fieldName' => 'stringValue', 'columnName' => 'string_value', 'type' => 'stringEnum']);
        $metadata->mapField(['fieldName' => 'nullableValue', 'columnName' => 'nullable_value', 'type' => 'stringEnum', 'nullable' => true]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIntValue(): DoctrineIntEnum
    {
        return $this->intValue;
    }

    public function getStringValue(): DoctrineStringEnum
    {
        return $this->stringValue;
    }

    public function getNullableValue(): ?DoctrineStringEnum
    {
        return $this->nullableValue;
    }
}
