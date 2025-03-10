<?php

declare(strict_types=1);

namespace App\FrameworkApp\Controller;

use Psr\Http\Message\ServerRequestInterface;

class ContactController
{
    public function show(ServerRequestInterface $request): string
    {
        return "<h1>Contact Us</h1>
                <p>This is a simple contact page.</p>
                <p>Email: contact@example.com</p>
                <p><a href='/'>Back to Home</a></p>";
    }
}