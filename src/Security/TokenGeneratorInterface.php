<?php

namespace App\Security;

interface TokenGeneratorInterface
{
    public function generateToken(int $pseudoBytesLength = 16): string;
}