<?php

declare(strict_types=1);

namespace App\FrameworkApp\Controller;

use App\Framework\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApiController extends AbstractController
{
    public function status(ServerRequestInterface $request): ResponseInterface
    {
        $data = [
            'status' => 'OK',
            'version' => '1.0.0',
            'timestamp' => time()
        ];

        return $this->json($data);
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

        return $this->json($data);
    }
}
