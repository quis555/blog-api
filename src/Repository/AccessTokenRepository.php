<?php

namespace App\Repository;

use App\Entity\AccessToken;
use Doctrine\ORM\EntityRepository;

class AccessTokenRepository extends EntityRepository
{
    public function findByToken(string $token): ?AccessToken
    {
        return $this->findOneBy(['token' => $token]);
    }
}