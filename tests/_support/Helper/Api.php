<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use App\Security\PasswordEncoderInterface;
use Codeception\Module\Db;
use DateTime;
use Psr\Container\ContainerInterface;

class Api extends \Codeception\Module
{
    private ?ContainerInterface $container = null;
    private Db $db;

    public function _initialize()
    {
        /** @var Db $db */
        $db = $this->moduleContainer->getModule('Db');
        $this->db = $db;
    }

    public function grabServiceFromContainer(string $key)
    {
        if ($this->container === null) {
            $this->container = require(__DIR__ . '/../../../app/bootstrap.php');
        }
        return $this->container->get($key);
    }

    public function haveUserInDatabase(): array
    {
        $generatedNum = random_int(1, 99999);
        $login = 'testUser_' . $generatedNum;
        $email = 'testUser_' . $generatedNum . '@test.com';
        $password = 'testUser' . $generatedNum;
        $displayName = 'Test User';
        $now = new DateTime();
        $id = $this->db->haveInDatabase('users', [
            'login' => $login,
            'email' => $email,
            'display_name' => $displayName,
            'password' => $this->grabServiceFromContainer(PasswordEncoderInterface::class)->encode($password),
            'registered_at' => $now->format('Y-m-d H:i:s'),
        ]);
        return ['id' => $id, 'login' => $login, 'email' => $email, 'password' => $password];
    }
}
