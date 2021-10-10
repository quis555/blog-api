<?php

namespace App\Controller;

use App\Api\Traits\JsonResponseTrait;
use App\Entity\User;
use App\Security\PasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Rakit\Validation\Validator;

class UserController
{
    use JsonResponseTrait;

    public function register(
        LoggerInterface $logger,
        ServerRequestInterface $request,
        EntityManagerInterface $entityManager,
        Validator $validator,
        PasswordEncoderInterface $encoder
    ): ResponseInterface {
        $logger->info('User register request');
        $validation = $validator->validate($request->getParsedBody(), [
            'login' => 'required|min:4|alpha_dash|unique:users,login',
            'email' => 'required|email|unique:users,email',
            'displayName' => 'required|min:4|alpha_spaces',
            'password' => 'required|min:6',
        ]);
        if ($validation->fails()) {
            $validationErrors = $validation->errors();
            $logger->notice('User registration bad request', ['errors' => json_encode($validationErrors)]);
            return $this->json(
                ['errors' => $validationErrors->toArray()],
                $validationErrors->has('email:unique') || $validationErrors->has('login:unique') ?
                    StatusCodeInterface::STATUS_CONFLICT : StatusCodeInterface::STATUS_BAD_REQUEST
            );
        }
        $data = $validation->getValidData();
        $entity = User::create(
            $data['login'], $data['email'], $data['displayName'],
            $encoder->encode($data['password'])
        );
        $entityManager->persist($entity);
        $entityManager->flush();
        $logger->info('User with id ' . $entity->getId() . ' registered');
        return $this->json(['id' => $entity->getId()], StatusCodeInterface::STATUS_CREATED);
    }
}