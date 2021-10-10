<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Yaml\Yaml;

class DocController
{
    public function index(ResponseInterface $response): ResponseInterface
    {
        $html = file_get_contents(__DIR__ . '/../../doc/redoc-static.html');
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    public function json(ResponseInterface $response): ResponseInterface
    {
        $yaml = Yaml::parseFile(__DIR__ . '/../../doc/openapi.yaml');
        $response->getBody()->write(json_encode($yaml));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function yaml(ResponseInterface $response): ResponseInterface
    {
        $yaml = file_get_contents(__DIR__ . '/../../doc/openapi.yaml');
        $response->getBody()->write($yaml);
        return $response->withHeader('Content-Type', 'application/x-yaml');
    }
}