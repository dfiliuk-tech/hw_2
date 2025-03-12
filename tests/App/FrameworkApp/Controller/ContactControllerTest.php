<?php

declare(strict_types=1);

namespace Tests\App\FrameworkApp\Controller;

use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\View\TwigService;
use App\FrameworkApp\Controller\ContactController;
use PHPUnit\Framework\TestCase;

class ContactControllerTest extends TestCase
{
    private ContactController $contactController;
    private TwigService $twig;
    private SecurityMiddleware $security;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(TwigService::class);
        $this->security = $this->createMock(SecurityMiddleware::class);

        // Configure the mock to return HTML content
        $this->twig->method('render')
            ->with('contact.html.twig')
            ->willReturn('<h1>Contact Us</h1><p>This is a simple contact page</p>');

        $this->contactController = new ContactController($this->twig, $this->security);
    }

    public function testShowReturnsValidResponse(): void
    {
        // Create a request
        $uri = new Uri('http://example.com/contact');
        $request = new ServerRequest('GET', $uri);

        // Call the show action
        $response = $this->contactController->show($request);

        // Assert it's a Response object
        $this->assertInstanceOf(Response::class, $response);

        // Assert status code
        $this->assertEquals(200, $response->getStatusCode());

        // Assert Content-Type header
        $this->assertStringContainsString(
            'text/html',
            $response->getHeaderLine('Content-Type')
        );

        // Get body content
        $body = (string) $response->getBody();

        // Assert body content
        $this->assertStringContainsString('<h1>Contact Us</h1>', $body);
    }

    public function testResponseIncludesExpectedContent(): void
    {
        // Create a request
        $uri = new Uri('http://example.com/contact');
        $request = new ServerRequest('GET', $uri);

        // Call the show action
        $response = $this->contactController->show($request);

        // Get body content
        $body = (string) $response->getBody();

        // Assert specific content elements
        $this->assertStringContainsString('This is a simple contact page', $body);
    }
}
