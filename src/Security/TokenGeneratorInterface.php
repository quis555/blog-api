<?php

namespace App\Security;

interface TokenGeneratorInterface
{
    public function generate(int $pseudoBytesLength = 16): string;
}