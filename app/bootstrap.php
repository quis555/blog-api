<?php

use DI\ContainerBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

const DEV_MODE = true;

ini_set('html_errors', 0);
if (DEV_MODE) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
} else {
    error_reporting(0);
}

$containerBuilder = new ContainerBuilder();

if (!DEV_MODE) {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// prepare dependencies
$dependencies = require __DIR__ . '/dependencies.php';
$dependencies($containerBuilder);

// build php-di container instance
/** @noinspection PhpUnhandledExceptionInspection */
return $containerBuilder->build();
