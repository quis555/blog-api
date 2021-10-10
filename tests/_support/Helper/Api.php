<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Psr\Container\ContainerInterface;

class Api extends \Codeception\Module
{
    private ?ContainerInterface $container = null;

    public function grabServiceFromContainer(string $key)
    {
        if ($this->container === null) {
            $this->container = require(__DIR__ . '/../../../app/bootstrap.php');
        }
        return $this->container->get($key);
    }
}
