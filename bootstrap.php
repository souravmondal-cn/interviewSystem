<?php

require_once './vendor/autoload.php';
require_once './local-settings.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/src"), $isDevMode);
$config->setFilterSchemaAssetsExpression("~^(?!wp_)~");
// obtaining the entity manager
$entityManager = EntityManager::create(array(
                'driver' => 'pdo_mysql',
                'host' => DB_HOST,
                'user' => DB_USER,
                'password' => DB_PASSWORD,
                'dbname' => DB_NAME,
                'charset' => 'utf8'
            ),
        $config
    );

/* @var $entityManager EntityManager */