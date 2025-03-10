<?php

declare(strict_types=1);

namespace App\FrameworkApp\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Framework\Http\Response;

class ApiController
{
    public function status(ServerRequestInterface $request): ResponseInterface
    {
        $data = [
            'status' => 'OK',
            'version' => '1.0.0',
            'timestamp' => time()
        ];

        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        // Simulate processing a POST request
        $body = $request->getParsedBody() ?? [];
        $status = $body['status'] ?? 'unknown';

        $data = [
            'status' => $status,
            'updated' => true,
            'timestamp' => time()
        ];

        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );
    }
}