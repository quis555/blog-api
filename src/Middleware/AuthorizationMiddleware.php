<?php

namespace App\Middleware;

use App\Repository\AccessTokenRepository;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Psr7\Response;

class AuthorizationMiddleware
{
    public function __construct(
        private AccessTokenRepository $accessTokenRepository
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return Response
     * @throws HttpUnauthorizedException
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeader('Authorization');
        if (empty($authHeader[0])) {
            throw new HttpUnauthorizedException($request, 'Authorization header not set.');
        }
        $accessToken = $this->accessTokenRepository->findByToken($authHeader[0]);
        if (!$accessToken) {
            throw new HttpUnauthorizedException($request, 'Authorization token is invalid.');
        }
        $now = new DateTimeImmutable();
        if ($accessToken->getExpiresAt() < $now) {
            throw new HttpUnauthorizedException($request, 'Authorization token expired.');
        }
        return $handler->handle($request->withAttribute('user', $accessToken->getUser()));
    }
}