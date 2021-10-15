<?php

namespace App\Security;

class TokenGenerator implements TokenGeneratorInterface
{
    public function generate(int $pseudoBytesLength = 16): string
    {
        return bin2hex(openssl_random_pseudo_bytes($pseudoBytesLength));
    }
}