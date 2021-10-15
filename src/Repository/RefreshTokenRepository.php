<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\ORM\EntityRepository;

class RefreshTokenRepository extends EntityRepository
{
    public function findByToken(string $token): ?RefreshToken
    {
        return $this->findOneBy([
            'token' => $token
        ]);
    }
}