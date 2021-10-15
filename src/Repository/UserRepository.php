<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findByLogin(string $login): ?User
    {
        return $this->findOneBy([
            'login' => $login,
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy([
            'email' => $email,
        ]);
    }
}