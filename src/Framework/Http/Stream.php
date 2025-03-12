<?php

declare(strict_types=1);

namespace App\Framework\Http;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use InvalidArgumentException;

/**
 * Implementation of PSR-7 StreamInterface for handling HTTP message bodies
 */
class Stream implements StreamInterface
{
    /**
     * @var resource|null
     */
    private $stream;

    /**
     * @var bool
     */
    private bool $seekable;

    /**
     * @var bool
     */
    private bool $readable;

    /**
     * @var bool
     */
    private bool $writable;

    /**
     * @var string|null
     * @phpstan-impure property.neverRead
     */
    // @phpstan-ignore-next-line
    private ?string $uri;

    /**
     * @var int|null
     */
    private ?int $size = null;

    /**
     * Create a new Stream instance
     *
     * @param resource|string $body Stream resource or string content
     */
    public function __construct($body = '')
    {
        if (is_string($body)) {
            $resource = fopen('php://temp', 'r+');
            if ($resource === false) {
                throw new RuntimeException('Could not create temp stream');
            }
            fwrite($resource, $body);
            fseek($resource, 0);
            $body = $resource;
        }

        if (!is_resource($body)) {
            throw new InvalidArgumentException('Stream must be a resource or string');
        }

        $this->stream = $body;
        $meta = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'];
        $this->readable = (str_contains($meta['mode'], 'r') || str_contains($meta['mode'], '+'));
        $this->writable = (str_contains($meta['mode'], 'w') ||
            str_contains($meta['mode'], 'a') ||
            str_contains($meta['mode'], '+'));
        $this->uri = $meta['uri'];
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->seek(0);
            }
            return $this->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $result = $this->stream;
        $this->stream = null;
        $this->size = null;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        $position = ftell($this->stream);
        if ($position === false) {
            throw new RuntimeException('Could not get stream position');
        }

        return $position;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        if (!isset($this->stream)) {
            return true;
        }

        return feof($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Could not seek to stream position ' . $offset);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->writable) {
            throw new RuntimeException('Stream is not writable');
        }

        $this->size = null;
        $result = fwrite($this->stream, $string);

        if ($result === false) {
            throw new RuntimeException('Error writing to stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new RuntimeException('Stream is not readable');
        }

        $result = fread($this->stream, $length);
        if ($result === false) {
            throw new RuntimeException('Error reading from stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (!isset($this->stream)) {
            throw new RuntimeException('Stream is detached');
        }

        if (!$this->readable) {
            throw new RuntimeException('Stream is not readable');
        }

        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new RuntimeException('Error reading stream contents');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key ? null : [];
        }

        $meta = stream_get_meta_data($this->stream);

        if ($key === null) {
            return $meta;
        }

        return $meta[$key] ?? null;
    }

    /**
     * Close the stream when the object is destroyed
     */
    public function __destruct()
    {
        $this->close();
    }
}
