<?php

declare(strict_types=1);

namespace App\FrameworkApp\Controller;

use App\Framework\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

class ContactController
{
    public function show(ServerRequestInterface $request): Response
    {

        return new Response(
            200,
            ['Content-Type' => 'text/html'],
            "<h1>Contact Us</h1>
                <p>This is a simple contact page.</p>
                <p>Email: contact@example.com</p>
                <p><a href='/'>Back to Home</a></p>"
        );
    }
}
