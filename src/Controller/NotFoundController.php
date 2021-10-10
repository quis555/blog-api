<?php

namespace App\Controller;

use App\Api\Traits\JsonResponseTrait;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

class NotFoundController
{
    use JsonResponseTrait;

    public function notFound(): ResponseInterface
    {
        return $this->json(['message' => 'Endpoint not found.'], StatusCodeInterface::STATUS_NOT_FOUND);
    }
}