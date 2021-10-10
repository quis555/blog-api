<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner as DoctrineConsoleRunner;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

return function (Application $application, ContainerInterface $container) {
    // application commands
//    $commands = [];
//
//    foreach ($commands as $command) {
//        /** @var Command $commandObject */
//        $commandObject = $container->get($command);
//        $application->add($commandObject);
//    }

    // doctrine commands
    $helperSet = $application->getHelperSet();
    $doctrineHelperSet = DoctrineConsoleRunner::createHelperSet($container->get(EntityManagerInterface::class));
    foreach ($doctrineHelperSet->getIterator() as $key => $doctrineHelper) {
        $helperSet->set($doctrineHelper, $key);
    }
    DoctrineConsoleRunner::addCommands($application);
};
