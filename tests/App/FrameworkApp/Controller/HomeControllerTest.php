<?php

declare(strict_types=1);

namespace Tests\App\FrameworkApp\Controller;

use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\FrameworkApp\Controller\HomeController;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
    private HomeController $homeController;

    protected function setUp(): void
    {
        $this->homeController = new HomeController();
    }

    public function testIndexReturnsValidResponse(): void
    {
        // Create a request
        $uri = new Uri('http://example.com/');
        $request = new ServerRequest('GET', $uri);

        // Call the index action
        $response = $this->homeController->index($request);

        // Assert it's a Response object
        $this->assertInstanceOf(Response::class, $response);

        // Assert status code
        $this->assertEquals(200, $response->getStatusCode());

        // Assert Content-Type header
        $this->assertEquals('text/html', $response->getHeaderLine('Content-Type'));

        // Get body content
        $body = (string) $response->getBody();

        // Assert body content
        $this->assertStringContainsString('<h1>Welcome to Our Minimal Framework</h1>', $body);
        $this->assertStringContainsString('This is the home page', $body);
    }

    public function testResponseIncludesNavigationLinks(): void
    {
        // Create a request
        $uri = new Uri('http://example.com/');
        $request = new ServerRequest('GET', $uri);

        // Call the index action
        $response = $this->homeController->index($request);

        // Get body content
        $body = (string) $response->getBody();

        // Verify all navigation links are present
        $this->assertStringContainsString('<a href=\'/api/status\'>API Status</a>', $body);
        $this->assertStringContainsString('<a href=\'/contact\'>Contact</a>', $body);
    }
}