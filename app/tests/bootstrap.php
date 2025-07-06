<?php

use App\DataFixtures\AppFixtures;
use App\Kernel;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/.env')) {
    // Lade zuerst die Basis .env
    (new Dotenv())->load(dirname(__DIR__) . '/.env');
}

if (file_exists(dirname(__DIR__) . '/.env.test')) {
    // Lade dann die .env.test, die die Basis-Werte überschreibt
    (new Dotenv())->load(dirname(__DIR__) . '/.env.test');
}

$kernel = new Kernel('test', true);
$kernel->boot();
$container = $kernel->getContainer();

$em = $container->get('doctrine')->getManager();
$connection = $em->getConnection();

$params = $connection->getParams();
$tmpParams = $params;
unset($tmpParams['dbname']);

$tmpConnection = DriverManager::getConnection($tmpParams);
$schemaManager = $tmpConnection->createSchemaManager();

$connection->close();
$connection->connect();

$metadata = $em->getMetadataFactory()->getAllMetadata();
$tool = new SchemaTool($em);
$tool->dropDatabase();
$tool->createSchema($metadata);

$loader = new Loader();
$loader->addFixture(new AppFixtures());

$executor = new ORMExecutor($em, new ORMPurger());
$executor->execute($loader->getFixtures());
