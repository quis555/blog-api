<?php

namespace App\Builder;

use App\Entity\AccessToken;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Security\SecurityConfig;
use App\Security\TokenGeneratorInterface;

class TokenEntityBuilder
{
    public function __construct(
        private TokenGeneratorInterface $tokenGenerator,
        private SecurityConfig $securityConfig,
    ) {
    }

    public function createAccessToken(User $user): AccessToken
    {
        return AccessToken::create(
            $user,
            $this->tokenGenerator->generateToken(32),
            $this->securityConfig->getAccessTokenLifetime()
        );
    }

    public function createRefreshToken(User $user): RefreshToken
    {
        return RefreshToken::create(
            $user,
            $this->tokenGenerator->generateToken(64),
            $this->securityConfig->getRefreshTokenLifetime()
        );
    }
}