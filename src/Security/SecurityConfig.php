<?php

namespace App\Security;

class SecurityConfig
{
    public function __construct(
        private int $accessTokenLifetime,
        private int $refreshTokenLifetime
    ) {
    }

    public function getAccessTokenLifetime(): int
    {
        return $this->accessTokenLifetime;
    }

    public function getRefreshTokenLifetime(): int
    {
        return $this->refreshTokenLifetime;
    }
}