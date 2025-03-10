<?php

declare(strict_types=1);

namespace App\FrameworkApp\Controller;

use Psr\Http\Message\ServerRequestInterface;
use App\Framework\Http\Response;

class HomeController
{
    public function index(ServerRequestInterface $request): Response
    {
        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            "<h1>Welcome to Our Minimal Framework</h1>
                <p>This is the home page of our application.</p>
                <ul>
                    <li><a href='/api/status'>API Status</a></li>
                    <li><a href='/contact'>Contact</a></li>
                </ul>"
        );
    }
}
