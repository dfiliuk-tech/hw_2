<?php

declare(strict_types=1);

namespace Tests\App\FrameworkApp\Controller;

use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\FrameworkApp\Controller\ContactController;
use PHPUnit\Framework\TestCase;

class ContactControllerTest extends TestCase
{
    private ContactController $contactController;

    protected function setUp(): void
    {
        $this->contactController = new ContactController();
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
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));

        // Get body content
        $body = (string) $response->getBody();

        // Assert body content
        $this->assertStringContainsString('<h1>Contact Us</h1>', $body);
        $this->assertStringContainsString('contact@example.com', $body);
        $this->assertStringContainsString('<a href=\'/\'>Back to Home</a>', $body);
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
        $this->assertStringContainsString('Email: contact@example.com', $body);
        $this->assertStringContainsString('<a href=\'/\'>Back to Home</a>', $body);
    }
}