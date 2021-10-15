<?php

namespace App\Controller;

use App\Api\Action\UserLoginAction;
use App\Api\Result\UserLoginResult;
use App\Api\Traits\CurrentUserTrait;
use App\Api\Traits\JsonResponseTrait;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Security\PasswordEncoderInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Rakit\Validation\Validator;

class UserController
{
    use JsonResponseTrait, CurrentUserTrait;

    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function register(
        ServerRequestInterface $request,
        EntityManagerInterface $entityManager,
        Validator $validator,
        PasswordEncoderInterface $encoder
    ): ResponseInterface {
        $this->logger->info('User register request');
        $validation = $validator->validate($request->getParsedBody(), [
            'login' => 'required|min:4|alpha_dash|unique:users,login',
            'email' => 'required|email|unique:users,email',
            'displayName' => 'required|min:4|alpha_spaces',
            'password' => 'required|min:6',
        ]);
        if ($validation->fails()) {
            $validationErrors = $validation->errors();
            $this->logger->notice('User registration bad request',
                ['errors' => json_encode($validationErrors->toArray())]);
            return $this->json(
                ['errors' => $validationErrors->toArray()],
                $validationErrors->has('email:unique') || $validationErrors->has('login:unique') ?
                    StatusCodeInterface::STATUS_CONFLICT : StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }
        $data = $validation->getValidData();
        $user = User::create(
            $data['login'], $data['email'], $data['displayName'],
            $encoder->encode($data['password'])
        );
        $entityManager->persist($user);
        $entityManager->flush();
        $this->logger->info('User successfully registered', [
            'userId' => $user->getId()
        ]);
        return $this->json(['id' => $user->getId()], StatusCodeInterface::STATUS_CREATED);
    }

    public function login(
        ServerRequestInterface $request,
        Validator $validator,
        PasswordEncoderInterface $passwordEncoder,
        UserRepository $userRepository,
        UserLoginAction $userLoginAction,
    ): ResponseInterface {
        $this->logger->info('User login (default) request');
        $requestBody = $request->getParsedBody();
        $loginIsEmail = filter_var($requestBody['login'] ?? null, FILTER_VALIDATE_EMAIL);

        $validation = $validator->validate($requestBody, [
            'login' => $loginIsEmail ? 'required|email' : 'required|min:4|alpha_dash',
            'password' => 'required|min:6',
        ]);
        if ($validation->fails()) {
            return $this->logBadRequestAndCreateBadRequestResponse(
                'User login (default) bad request',
                $validation->errors()->toArray()
            );
        }

        $data = $validation->getValidData();
        $user = $loginIsEmail ? $userRepository->findByEmail($data['login']) : $userRepository->findByLogin($data['login']);
        if (!$user || !$passwordEncoder->verify($data['password'], $user->getPassword())) {
            return $this->getUserNotFoundResponse();
        }
        $loginResult = $userLoginAction->execute($user);
        return $this->createLoginResponse($loginResult);
    }

    public function loginWithRefreshToken(
        ServerRequestInterface $request,
        Validator $validator,
        RefreshTokenRepository $refreshTokenRepository,
        UserLoginAction $userLoginAction,
    ): ResponseInterface {
        $this->logger->info('User login (with refresh token) request');
        $validation = $validator->validate($request->getParsedBody(), [
            'refreshToken' => 'required|min:20|alpha_num',
        ]);
        if ($validation->fails()) {
            return $this->logBadRequestAndCreateBadRequestResponse(
                'User login (with refresh token) bad request',
                $validation->errors()->toArray()
            );
        }
        $refreshToken = $refreshTokenRepository->findByToken($validation->getValidData()['refreshToken']);
        $now = new DateTimeImmutable();
        if (!$refreshToken || $refreshToken->getExpiresAt() < $now || $refreshToken->isUsed()) {
            return $this->json(['message' => 'Invalid token'], StatusCodeInterface::STATUS_NOT_FOUND);
        }
        $refreshToken->markTokenAsUsed();
        $user = $refreshToken->getUser();
        $loginResult = $userLoginAction->execute($user);
        return $this->createLoginResponse($loginResult);
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('Get user request');
        $user = $this->getCurrentUser($request);
        if (!$user) {
            return $this->getUserNotFoundResponse();
        }
        return $this->json([
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'email' => $user->getEmail(),
            'displayName' => $user->getDisplayName(),
            'registeredAt' => $user->getRegisteredAt()->format(DateTimeInterface::ATOM),
            'lastLoginAt' => $user->getLastLoginAt()?->format(DateTimeInterface::ATOM),
        ]);
    }

    private function getUserNotFoundResponse(): ResponseInterface
    {
        return $this->json(['message' => 'User not found'], StatusCodeInterface::STATUS_NOT_FOUND);
    }

    private function logBadRequestAndCreateBadRequestResponse(
        string $message,
        array $errors = []
    ): ResponseInterface {
        $this->logger->notice($message, ['errors' => json_encode($errors)]);
        return $this->json(['errors' => $errors], StatusCodeInterface::STATUS_BAD_REQUEST);
    }

    private function createLoginResponse(UserLoginResult $loginResult): ResponseInterface
    {
        return $this->json(
            [
                'accessToken' => [
                    'token' => $loginResult->getAccessToken()->getToken(),
                    'expiresAt' => $loginResult->getAccessToken()->getExpiresAt()->format(DateTimeInterface::ATOM),
                ],
                'refreshToken' => [
                    'token' => $loginResult->getRefreshToken()->getToken(),
                    'expiresAt' => $loginResult->getRefreshToken()->getExpiresAt()->format(DateTimeInterface::ATOM),
                ],
            ],
            StatusCodeInterface::STATUS_CREATED
        );
    }
}