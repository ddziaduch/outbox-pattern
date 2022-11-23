<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;
use Doctrine\Persistence\ObjectManager;
use MongoDB\Client;

class ObjectManagerFactory
{
    public function create(Client $client, EventManager $eventManager): ObjectManager
    {
        $config = new Configuration();
        $config->setProxyDir(__DIR__ . '/Proxies');
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir(__DIR__ . '/Hydrators');
        $config->setHydratorNamespace('Hydrators');
        $config->setDefaultDB('outbox-pattern');
        $config->setMetadataDriverImpl(
            AttributeDriver::create(__DIR__ . '/Documents'),
        );

        return DocumentManager::create($client, $config, $eventManager);
    }
}
