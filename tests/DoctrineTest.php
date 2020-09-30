<?php
declare(strict_types=1);
namespace Thunder\Platenum\Tests;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Thunder\Platenum\Doctrine\PlatenumDoctrineType;
use Thunder\Platenum\Tests\Fake\DoctrineEntity;
use Thunder\Platenum\Tests\Fake\DoctrineIntEnum;
use Thunder\Platenum\Tests\Fake\DoctrineStringEnum;

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
}
