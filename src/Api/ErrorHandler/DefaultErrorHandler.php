<?php

namespace App\Api\ErrorHandler;

use App\Api\Traits\JsonResponseTrait;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpException;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

class DefaultErrorHandler implements ErrorHandlerInterface
{
    use JsonResponseTrait;

    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        $statusCode = $this->determineStatusCode($request->getMethod(), $exception);

        $responseBody = [
            'message' => $exception instanceof HttpException ? $exception->getMessage() : 'Unknown server error'
        ];

        if ($displayErrorDetails || $logErrorDetails) {
            $exceptionContext = $this->createExceptionContext($exception);
        }
        if ($logErrors) {
            $this->logger->error(
                'Error "' . $exception->getMessage() . '" of type ' . get_class($exception) . ' in file ' . $exception->getFile() . ' at line ' . $exception->getLine(),
                $logErrorDetails ? $exceptionContext : []
            );
        }
        if ($displayErrorDetails) {
            $responseBody += ['exception' => $exceptionContext];
        }
        return $this->json($responseBody, $statusCode);
    }

    protected function createExceptionContext(Throwable $throwable): array
    {
        return [
            'type' => get_class($throwable),
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'code' => $throwable->getCode(),
        ];
    }

    protected function determineStatusCode(string $method, Throwable $exception): int
    {
        if ($method === 'OPTIONS') {
            return StatusCodeInterface::STATUS_OK;
        }

        if ($exception instanceof HttpException) {
            return $exception->getCode();
        }

        return StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    }
}