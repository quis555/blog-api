<?php

use App\Security\PasswordEncoder;
use App\Security\PasswordEncoderInterface;
use App\Validation\Rule\UniqueRule;
use DI\ContainerBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Monolog\Handler\ElasticsearchHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\Tools\Setup;
use Psr\Log\LoggerInterface;
use Rakit\Validation\Validator;
use Symfony\Component\Yaml\Yaml;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'config.db.main' => function () {
            $config = Yaml::parseFile(__DIR__ . '/../config/db.yaml');
            return $config['main'];
        },
        'config.es.main' => function () {
            $config = Yaml::parseFile(__DIR__ . '/../config/elasticsearch.yaml');
            return $config['main'];
        },
        'config.logger.main' => function () {
            $config = Yaml::parseFile(__DIR__ . '/../config/logger.yaml');
            return $config['main'];
        },
        Client::class => function (ContainerInterface $container) {
            $config = $container->get('config.es.main');
            return ClientBuilder::fromConfig($config);
        },
        LoggerInterface::class => function (ContainerInterface $container) {
            $config = $container->get('config.logger.main');
            $logger = new Logger($config['app-name']);
            $handler = new ElasticsearchHandler($container->get(Client::class));
            $logger->pushHandler($handler);
            $logger->pushProcessor(new UidProcessor());
            if (php_sapi_name() !== 'cli') {
                $logger->pushProcessor(new WebProcessor());
            }
            return $logger;
        },
        Connection::class => function (ContainerInterface $container) {
            return DriverManager::getConnection($container->get('config.db.main'));
        },
        EntityManagerInterface::class => function (ContainerInterface $container) {
            $proxyDir = null;
            $cache = null;
            $useSimpleAnnotationReader = false;
            $metadataConfig = Setup::createAnnotationMetadataConfiguration(
                [__DIR__ . '/../src/Entity'],
                DEV_MODE, $proxyDir, $cache, $useSimpleAnnotationReader
            );
            return EntityManager::create($container->get('config.db.main'), $metadataConfig);
        },
        Validator::class => function (ContainerInterface $container) {
            $validator = new Validator;
            $validator->setUseHumanizedKeys(false);
            $validator->addValidator('unique', $container->get(UniqueRule::class));
            return $validator;
        },
        PasswordEncoderInterface::class => function () {
            return new PasswordEncoder();
        },
    ]);
};