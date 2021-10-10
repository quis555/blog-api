<?php

namespace App\Security;

class PasswordEncoder implements PasswordEncoderInterface
{
    public function encode(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}