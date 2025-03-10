<?php

declare(strict_types=1);

namespace Tests\App\FrameworkApp\Controller;

use App\Framework\Http\ServerRequest;
use App\Framework\Http\Uri;
use App\FrameworkApp\Controller\ApiController;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ApiControllerTest extends TestCase
{
    private ApiController $apiController;

    protected function setUp(): void
    {
        $this->apiController = new ApiController();
    }

    public function testStatusReturnsValidJsonResponse(): void
    {
        // Create a request
        $uri = new Uri('http://example.com/api/status');
        $request = new ServerRequest('GET', $uri);

        // Call the status action
        $response = $this->apiController->status($request);

        // Assert basic response structure
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        // Decode the response body
        $content = json_decode((string)$response->getBody(), true);
        
        // Assert the JSON structure
        $this->assertIsArray($content);
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('version', $content);
        $this->assertArrayHasKey('timestamp', $content);
        
        // Assert the content values
        $this->assertEquals('OK', $content['status']);
        $this->assertEquals('1.0.0', $content['version']);
        $this->assertIsInt($content['timestamp']);
    }

    public function testUpdateReturnsValidJsonResponseWithDefaultStatus(): void
    {
        // Create a request with empty body
        $uri = new Uri('http://example.com/api/status');
        $request = new ServerRequest('POST', $uri);

        // Call the update action
        $response = $this->apiController->update($request);

        // Assert basic response structure
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaderLine('Content-Type'));

        // Decode the response body
        $content = json_decode((string)$response->getBody(), true);
        
        // Assert the JSON structure
        $this->assertIsArray($content);
        $this->assertArrayHasKey('status', $content);
        $this->assertArrayHasKey('updated', $content);
        $this->assertArrayHasKey('timestamp', $content);
        
        // Assert the content values
        $this->assertEquals('unknown', $content['status']);
        $this->assertTrue($content['updated']);
        $this->assertIsInt($content['timestamp']);
    }

    public function testUpdateReturnsValidJsonResponseWithCustomStatus(): void
    {
        // Create a request with custom status in body
        $uri = new Uri('http://example.com/api/status');
        $request = new ServerRequest('POST', $uri);
        
        // Create a request with body parameters
        $request = $request->withParsedBody(['status' => 'maintenance']);

        // Call the update action
        $response = $this->apiController->update($request);

        // Decode the response body
        $content = json_decode((string)$response->getBody(), true);
        
        // Assert the status is updated correctly
        $this->assertEquals('maintenance', $content['status']);
        $this->assertTrue($content['updated']);
    }
}
