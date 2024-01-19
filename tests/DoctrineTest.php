<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Thunder\Platenum\Doctrine\PlatenumDoctrineType;
use Thunder\Platenum\Tests\Fake\DoctrineEntity;
use Thunder\Platenum\Tests\Fake\DoctrineIntEnum;
use Thunder\Platenum\Tests\Fake\DoctrineStringEnum;
use Thunder\Platenum\Tests\Fake\NoTraitEnum;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class DoctrineTest extends AbstractTestCase
{
    public function testCreateFromMember(): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'dbname' => ':memory:']);
        $connection->exec('CREATE TABLE doctrine_entity (
            id INTEGER NOT NULL PRIMARY KEY,
            int_value INTEGER NOT NULL,
            string_value VARCHAR(20) NOT NULL,
            nullable_value VARCHAR(20) NULL
        )');
        $configuration = new Configuration();
        $configuration->setMetadataDriverImpl(new StaticPHPDriver([__DIR__.'/Fake']));
        $configuration->setProxyDir(__DIR__.'/../var/doctrine');
        $configuration->setProxyNamespace('Platenum\\Doctrine');

        PlatenumDoctrineType::registerInteger('intEnum', DoctrineIntEnum::class);
        PlatenumDoctrineType::registerString('stringEnum', DoctrineStringEnum::class);

        $entity = new DoctrineEntity(1337, DoctrineIntEnum::FIRST(), DoctrineStringEnum::TWO());
        $em = EntityManager::create($connection, $configuration);
        $em->persist($entity);
        $em->flush();
        $em->clear();

        $foundEntity = $em->find(DoctrineEntity::class, 1337);
        $this->assertInstanceOf(DoctrineEntity::class, $foundEntity);
        $this->assertSame($entity->getId(), $foundEntity->getId());
        $this->assertSame($entity->getIntValue(), $foundEntity->getIntValue());
        $this->assertSame($entity->getStringValue(), $foundEntity->getStringValue());
        $this->assertNull($foundEntity->getNullableValue());
    }

    public function testDoctrineType(): void
    {
        PlatenumDoctrineType::registerInteger('intEnum0', DoctrineIntEnum::class);
        $intType = PlatenumDoctrineType::getType('intEnum0');

        $platform = new MySQL80Platform();
        $this->assertTrue($intType->requiresSQLCommentHint($platform));
        $this->assertSame('intEnum0', $intType->getName());
        $this->assertSame('INT', $intType->getSQLDeclaration([], $platform));

        PlatenumDoctrineType::registerString('stringEnum0', DoctrineStringEnum::class);
        $stringType = PlatenumDoctrineType::getType('stringEnum0');
        $this->assertSame('VARCHAR(255)', $stringType->getSQLDeclaration([], $platform));
    }

    public function testInvalidClass(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('PlatenumDoctrineType allows only Platenum enumerations, `stdClass` given.');
        PlatenumDoctrineType::registerInteger('invalid', \stdClass::class);
    }

    public function testDuplicateAlias(): void
    {
        PlatenumDoctrineType::registerString('enumX', DoctrineIntEnum::class);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Alias `'.DoctrineIntEnum::class.'` was already registered in PlatenumDoctrineType.');
        PlatenumDoctrineType::registerString('enumX', DoctrineIntEnum::class);
    }

    public function testImpossibleDatabaseConversionWithUnsupportedValue(): void
    {
        PlatenumDoctrineType::registerString('impossibleEnumConvert', DoctrineIntEnum::class);
        $this->expectException(\LogicException::class);
        PlatenumDoctrineType::getType('impossibleEnumConvert')->convertToDatabaseValue('not an object', new MySql80Platform());
    }

    public function testImpossibleValueConversionCast(): void
    {
        PlatenumDoctrineType::registerString('impossibleEnumCast', DoctrineIntEnum::class);
        $result = PlatenumDoctrineType::getType('impossibleEnumCast')->convertToDatabaseValue(DoctrineIntEnum::FIRST(), new MySql80Platform());
        $this->assertSame('1', $result, $result);
    }

    public function testNoTrait(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('PlatenumDoctrineType allows only Platenum enumerations, `'.NoTraitEnum::class.'` given.');
        PlatenumDoctrineType::registerString('noTraitEnum', NoTraitEnum::class);
    }
}
