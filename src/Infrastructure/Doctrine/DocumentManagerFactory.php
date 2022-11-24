<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use MongoDB\Client;

class DocumentManagerFactory
{
    public function create(Client $client, EventManager $eventManager): DocumentManager
    {
        $config = new Configuration();
        $config->setProxyDir(__DIR__ . '/Proxies');
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir(__DIR__ . '/Hydrators');
        $config->setHydratorNamespace('Hydrators');
        $config->setDefaultDB('outbox-pattern');

        $driverImpl = AttributeDriver::create(__DIR__ . '/Documents');
        assert($driverImpl instanceof MappingDriver);
        $config->setMetadataDriverImpl($driverImpl);

        return DocumentManager::create($client, $config, $eventManager);
    }
}
