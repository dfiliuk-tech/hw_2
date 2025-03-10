<?php

declare(strict_types=1);

namespace Tests\App\FrameworkApp;

use PHPUnit\Framework\TestCase;

/**
 * Functional tests using cURL to test the application directly.
 *
 * This approach doesn't require any additional dependencies beyond PHP's curl extension.
 * Make sure your Docker environment is running before executing these tests.
 */
class CurlFunctionalTest extends TestCase
{
    private string $baseUrl = 'http://localhost:8000';

    /**
     * Helper method to make HTTP requests using cURL
     *
     * @param string $method HTTP method (GET, POST, etc)
     * @param string $path URL path to request
     * @param array $data Data to send (for POST, PUT, etc)
     * @param array $headers HTTP headers to send
     * @return array Response with status code, headers, and body
     */
    private function request(string $method, string $path, array $data = [], array $headers = []): array
    {
        $ch = curl_init();

        // Set URL
        $url = $this->baseUrl . $path;
        curl_setopt($ch, CURLOPT_URL, $url);

        // Set method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Handle data for POST/PUT requests
        if ($method === 'POST' || $method === 'PUT') {
            $postFields = http_build_query($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

            // Add content type header if not provided
            if (!isset($headers['Content-Type'])) {
                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        // Set headers
        if (!empty($headers)) {
            $curlHeaders = [];
            foreach ($headers as $name => $value) {
                $curlHeaders[] = "$name: $value";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        }

        // Set options to get response headers
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Execute request
        $response = curl_exec($ch);

        // Check for errors
        if ($response === false) {
            curl_close($ch);
            return [
                'status' => 0,
                'headers' => [],
                'body' => 'cURL Error: ' . curl_error($ch)
            ];
        }

        // Get status code
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Get header size and extract headers and body
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerText = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        // Parse headers
        $headers = [];
        foreach (explode("\r\n", $headerText) as $i => $line) {
            if ($i === 0 || empty($line)) {
                continue; // Skip status line and empty lines
            }

            list($name, $value) = explode(': ', $line, 2);
            $headers[$name] = $value;
        }

        curl_close($ch);

        return [
            'status' => $statusCode,
            'headers' => $headers,
            'body' => $body
        ];
    }

    /**
     * Verify the app is running
     */
    protected function verifyAppIsRunning(): void
    {
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 0) {
            $this->markTestSkipped('Application server is not running. Start Docker with `make up` or `docker-compose up -d`.');
        }
    }

    /**
     * Test that the home page returns a successful response with expected content
     */
    public function testHomePage(): void
    {
        $this->verifyAppIsRunning();

        $response = $this->request('GET', '/');

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('Content-type', $response['headers']);
        $this->assertStringContainsString('text/html', $response['headers']['Content-type']);

        $this->assertStringContainsString('<h1>Welcome to Our Minimal Framework</h1>', $response['body']);
        $this->assertStringContainsString('<a href=\'/api/status\'>', $response['body']);
        $this->assertStringContainsString('<a href=\'/contact\'>', $response['body']);
    }

    /**
     * Test that the contact page returns a successful response with expected content
     */
    public function testContactPage(): void
    {
        $this->verifyAppIsRunning();

        $response = $this->request('GET', '/contact');

        $this->assertEquals(200, $response['status']);
        $this->assertStringContainsString('<h1>Contact Us</h1>', $response['body']);
        $this->assertStringContainsString('contact@example.com', $response['body']);
    }

    /**
     * Test that the API status endpoint returns a valid JSON response
     */
    public function testApiStatusEndpoint(): void
    {
        $this->verifyAppIsRunning();

        $response = $this->request('GET', '/api/status');

        $this->assertEquals(200, $response['status']);
        $this->assertStringContainsString('application/json', $response['headers']['Content-Type']);

        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('OK', $data['status']);
        $this->assertArrayHasKey('version', $data);
        $this->assertArrayHasKey('timestamp', $data);
    }

    /**
     * Test that the API update endpoint properly processes POSTed data
     */
    public function testApiUpdateEndpoint(): void
    {
        $this->verifyAppIsRunning();

        $response = $this->request('POST', '/api/status', [
            'status' => 'maintenance'
        ]);

        $this->assertEquals(200, $response['status']);

        $data = json_decode($response['body'], true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('maintenance', $data['status']);
        $this->assertArrayHasKey('updated', $data);
        $this->assertTrue($data['updated']);
    }

    /**
     * Test that non-existent routes return a 404 response
     */
    public function testNonExistentRoute(): void
    {
        $this->verifyAppIsRunning();

        $response = $this->request('GET', '/non-existent-page');

        $this->assertEquals(404, $response['status']);
        $this->assertStringContainsString('Not Found', $response['body']);
    }

    /**
     * Test that using incorrect HTTP methods returns an error
     */
    public function testMethodNotAllowed(): void
    {
        $this->verifyAppIsRunning();

        $response = $this->request('POST', '/contact');

        // Since your application returns 404 for method not allowed (rather than 405)
        $this->assertEquals(404, $response['status']);
    }
}
