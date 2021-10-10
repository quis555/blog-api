<?php

namespace App\Api\Traits;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

trait JsonResponseTrait
{
    protected function json(array $data, int $statusCode = StatusCodeInterface::STATUS_OK): ResponseInterface {
        $response = new Response();
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }
}