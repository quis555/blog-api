<?php

use App\Entity\AccessToken;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $doctrineRepositories = [
        UserRepository::class => User::class,
        AccessTokenRepository::class => AccessToken::class,
        RefreshTokenRepository::class => RefreshToken::class,
    ];

    $definitions = [];
    foreach ($doctrineRepositories as $doctrineRepositoryClass => $entityClass) {
        $definitions[$doctrineRepositoryClass] = function (ContainerInterface $container) use ($entityClass) {
            return $container->get(EntityManagerInterface::class)->getRepository($entityClass);
        };
    }
    $containerBuilder->addDefinitions($definitions);
};
