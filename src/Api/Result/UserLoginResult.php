<?php

namespace App\Api\Result;

use App\Entity\AccessToken;
use App\Entity\RefreshToken;

class UserLoginResult
{
    public function __construct(
        private AccessToken $accessToken,
        private RefreshToken $refreshToken
    ) {
    }

    public function getAccessToken(): AccessToken
    {
        return $this->accessToken;
    }

    public function getRefreshToken(): RefreshToken
    {
        return $this->refreshToken;
    }
}