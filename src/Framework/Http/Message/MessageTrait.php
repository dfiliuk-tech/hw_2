<?php

declare(strict_types=1);

namespace App\Framework\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/**
 * Trait implementing functionality common to requests and responses
 */
trait MessageTrait
{
    /**
     * @var array<string, string[]>
     */
    private array $headers = [];

    /**
     * @var array<string, string>
     */
    private array $headerNames = [];

    /**
     * @var string
     */
    private string $protocolVersion = '1.1';

    /**
     * @var StreamInterface|null
     */
    private ?StreamInterface $stream = null;

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version): MessageInterface
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name): bool
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name): array
    {
        $normalized = strtolower($name);

        if (!isset($this->headerNames[$normalized])) {
            return [];
        }

        $name = $this->headerNames[$normalized];

        return $this->headers[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name): string
    {
        $value = $this->getHeader($name);

        if (empty($value)) {
            return '';
        }

        return implode(', ', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value): MessageInterface
    {
        $normalized = strtolower($name);

        $new = clone $this;

        if (isset($new->headerNames[$normalized])) {
            $header = $new->headerNames[$normalized];
            unset($new->headers[$header]);
        }

        if (is_array($value)) {
            $value = array_values(array_map('strval', $value));
        } else {
            $value = [(string) $value];
        }

        $new->headerNames[$normalized] = $name;
        $new->headers[$name] = $value;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        if (!is_array($value)) {
            $value = [(string) $value];
        }

        $normalized = strtolower($name);

        $new = clone $this;

        if (isset($new->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            $new->headers[$header] = array_merge($this->headers[$header], $value);
        } else {
            $new->headerNames[$normalized] = $name;
            $new->headers[$name] = $value;
        }

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name): MessageInterface
    {
        $normalized = strtolower($name);

        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $header = $this->headerNames[$normalized];

        $new = clone $this;
        unset($new->headers[$header], $new->headerNames[$normalized]);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        if ($this->stream === null) {
            $this->stream = new Stream('');
        }

        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        if ($this->stream === $body) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    /**
     * Set headers from array
     *
     * @param array<string, string|string[]> $headers Headers to set
     */
    private function setHeaders(array $headers): void
    {
        $this->headerNames = $this->headers = [];

        foreach ($headers as $header => $value) {
            $this->assertHeader($header);
            $value = $this->normalizeHeaderValue($value);
            $normalized = strtolower($header);

            $this->headerNames[$normalized] = $header;
            $this->headers[$header] = $value;
        }
    }

    /**
     * Assert header name is valid
     *
     * @param mixed $header Header name to validate
     * @throws InvalidArgumentException For invalid header names
     */
    private function assertHeader($header): void
    {
        if (!is_string($header) || !preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $header)) {
            throw new InvalidArgumentException('Invalid header name');
        }
    }

    /**
     * Normalize header value
     *
     * @param mixed $value Header value
     * @return string[] Normalized header value
     * @throws InvalidArgumentException For invalid header values
     */
    private function normalizeHeaderValue($value): array
    {
        if (!is_array($value)) {
            $value = [(string) $value];
        }

        $normalized = [];

        foreach ($value as $item) {
            if (!is_string($item) && !is_numeric($item)) {
                throw new InvalidArgumentException('Invalid header value');
            }

            $normalized[] = (string) $item;
        }

        return $normalized;
    }
}
