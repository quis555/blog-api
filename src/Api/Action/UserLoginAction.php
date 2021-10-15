<?php

namespace App\Api\Action;

use App\Api\Result\UserLoginResult;
use App\Builder\TokenEntityBuilder;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UserLoginAction
{
    public function __construct(
        private TokenEntityBuilder $tokenEntityBuilder,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    public function execute(User $user): UserLoginResult
    {
        $accessToken = $this->tokenEntityBuilder->createAccessToken($user);
        $this->entityManager->persist($accessToken);
        $refreshToken = $this->tokenEntityBuilder->createRefreshToken($user);
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();
        $this->logger->info('User successfully logged in', [
            'userId' => $user->getId(),
            'accessTokenId' => $accessToken->getId(),
            'refreshTokenId' => $refreshToken->getId(),
        ]);
        return new UserLoginResult($accessToken, $refreshToken);
    }
}