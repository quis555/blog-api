<?php

namespace App\Api\Traits;

use App\Entity\User;
use Psr\Http\Message\ServerRequestInterface;

trait CurrentUserTrait
{
    protected function getCurrentUser(ServerRequestInterface $request): ?User
    {
        return $request->getAttribute('user');
    }
}