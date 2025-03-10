<?php

declare(strict_types=1);

namespace App\Framework\Http;

use Psr\Http\Message\UriInterface;
use InvalidArgumentException;

/**
 * PSR-7 URI implementation
 */
class Uri implements UriInterface
{
    /**
     * @var string
     */
    private string $scheme = '';

    /**
     * @var string
     */
    private string $userInfo = '';

    /**
     * @var string
     */
    private string $host = '';

    /**
     * @var int|null
     */
    private ?int $port = null;

    /**
     * @var string
     */
    private string $path = '';

    /**
     * @var string
     */
    private string $query = '';

    /**
     * @var string
     */
    private string $fragment = '';

    /**
     * Create a new URI from string
     *
     * @param string $uri URI string
     */
    public function __construct(string $uri = '')
    {
        if ($uri !== '') {
            $parts = parse_url($uri);

            if ($parts === false) {
                throw new InvalidArgumentException('Unable to parse URI: ' . $uri);
            }

            $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
            $this->userInfo = isset($parts['user']) ? $parts['user'] : '';
            $this->userInfo .= isset($parts['pass']) ? ':' . $parts['pass'] : '';
            $this->host = isset($parts['host']) ? strtolower($parts['host']) : '';
            $this->port = isset($parts['port']) ? $parts['port'] : null;
            $this->path = isset($parts['path']) ? $parts['path'] : '';
            $this->query = isset($parts['query']) ? $parts['query'] : '';
            $this->fragment = isset($parts['fragment']) ? $parts['fragment'] : '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }

        $authority = $this->host;

        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme): UriInterface
    {
        $scheme = (string) $scheme;
        $scheme = strtolower($scheme);

        if ($this->scheme === $scheme) {
            return $this;
        }

        $new = clone $this;
        $new->scheme = $scheme;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null): UriInterface
    {
        $info = $user;
        if ($password !== null && $password !== '') {
            $info .= ':' . $password;
        }

        if ($this->userInfo === $info) {
            return $this;
        }

        $new = clone $this;
        $new->userInfo = $info;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host): UriInterface
    {
        $host = (string) $host;
        $host = strtolower($host);

        if ($this->host === $host) {
            return $this;
        }

        $new = clone $this;
        $new->host = $host;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port): UriInterface
    {
        if ($port !== null) {
            $port = (int) $port;
            if ($port < 1 || $port > 65535) {
                throw new InvalidArgumentException('Invalid port: ' . $port);
            }
        }

        if ($this->port === $port) {
            return $this;
        }

        $new = clone $this;
        $new->port = $port;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path): UriInterface
    {
        $path = (string) $path;

        if (strpos($path, '?') !== false || strpos($path, '#') !== false) {
            throw new InvalidArgumentException('Path cannot contain query or fragment');
        }

        if ($this->path === $path) {
            return $this;
        }

        $new = clone $this;
        $new->path = $path;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query): UriInterface
    {
        $query = (string) $query;
        if ($query !== '' && $query[0] === '?') {
            $query = substr($query, 1);
        }

        if (strpos($query, '#') !== false) {
            throw new InvalidArgumentException('Query cannot contain fragments');
        }

        if ($this->query === $query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $query;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment): UriInterface
    {
        $fragment = (string) $fragment;
        if ($fragment !== '' && $fragment[0] === '#') {
            $fragment = substr($fragment, 1);
        }

        if ($this->fragment === $fragment) {
            return $this;
        }

        $new = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        $uri = '';

        if ($this->scheme !== '') {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();
        if ($authority !== '') {
            $uri .= '//' . $authority;
        }

        $path = $this->path;
        if ($path !== '') {
            if ($path[0] !== '/') {
                if ($authority !== '') {
                    $path = '/' . $path;
                }
            }
            $uri .= $path;
        }

        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }

        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }
}
