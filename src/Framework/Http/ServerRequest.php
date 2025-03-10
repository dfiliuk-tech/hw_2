<?php

declare(strict_types=1);

namespace App\Framework\Http;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use InvalidArgumentException;

/**
 * PSR-7 server request implementation
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $attributes = [];
    
    /**
     * @var array<string, mixed>
     */
    private array $cookieParams = [];
    
    /**
     * @var array<string, mixed>
     */
    private array $serverParams = [];
    
    /**
     * @var array<string, mixed>
     */
    private array $queryParams = [];
    
    /**
     * @var array<string, UploadedFileInterface>
     */
    private array $uploadedFiles = [];
    
    /**
     * @var null|array<string, mixed>|object
     */
    private $parsedBody;
    
    /**
     * Create a new server request
     *
     * @param string $method HTTP method
     * @param UriInterface|string $uri URI
     * @param array<string, string|string[]> $headers Request headers
     * @param StreamInterface|string|null $body Request body
     * @param string $version Protocol version
     * @param array<string, mixed> $serverParams Server parameters
     */
    public function __construct(
        string $method,
        $uri,
        array $headers = [],
        $body = null,
        string $version = '1.1',
        array $serverParams = []
    ) {
        parent::__construct($method, $uri, $headers, $body, $version);
        $this->serverParams = $serverParams;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookieParams = $cookies;
        
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new = clone $this;
        $new->queryParams = $query;
        
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;
        
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        if (!is_array($data) && !is_object($data) && $data !== null) {
            throw new InvalidArgumentException('Parsed body must be array, object or null');
        }
        
        $new = clone $this;
        $new->parsedBody = $data;
        
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        
        return $default;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withAttribute($name, $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        
        return $new;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($name): ServerRequestInterface
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }
        
        $new = clone $this;
        unset($new->attributes[$name]);
        
        return $new;
    }
    
    /**
     * Create a ServerRequest from global variables
     *
     * @return ServerRequestInterface
     */
    public static function fromGlobals(): ServerRequestInterface
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $uri = self::createUriFromGlobals($_SERVER);
        $body = new Stream('php://input');
        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';
        
        $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $_SERVER);
        
        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST);
    }
    
    /**
     * Create a Uri from global server variables
     *
     * @param array<string, mixed> $server Server variables
     * @return Uri
     */
    private static function createUriFromGlobals(array $server): Uri
    {
        $uri = new Uri();
        
        // HTTP host
        if (isset($server['HTTP_HOST'])) {
            $uri = $uri->withHost($server['HTTP_HOST']);
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        }
        
        // HTTPS
        if (isset($server['HTTPS']) && $server['HTTPS'] !== 'off') {
            $uri = $uri->withScheme('https');
        } else {
            $uri = $uri->withScheme('http');
        }
        
        // Port
        if (isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort((int)$server['SERVER_PORT']);
        }
        
        // Path
        $requestUri = $server['REQUEST_URI'] ?? '';
        if ($requestUri !== '') {
            $requestUri = preg_replace('#^[^/:]+://[^/]+#', '', $requestUri);
            $uriParts = explode('?', $requestUri, 2);
            $uri = $uri->withPath($uriParts[0]);
            if (isset($uriParts[1])) {
                $uri = $uri->withQuery($uriParts[1]);
            }
        }
        
        return $uri;
    }
}
