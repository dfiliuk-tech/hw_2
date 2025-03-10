<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use App\Framework\Http\Uri;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class UriTest extends TestCase
{
    public function testCreateEmptyUri(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', $uri->getFragment());
        $this->assertSame('', (string)$uri);
    }

    public function testParseUri(): void
    {
        $uri = new Uri('https://user:pass@example.com:8080/path/123?q=abc&x=y#test');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/123', $uri->getPath());
        $this->assertSame('q=abc&x=y', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://user:pass@example.com:8080/path/123?q=abc&x=y#test', (string)$uri);
    }

    public function testWithScheme(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withScheme('https');

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('https', $new->getScheme());
        $this->assertSame('https://example.com', (string)$new);
    }

    public function testWithUserInfo(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withUserInfo('user', 'pass');

        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('user:pass', $new->getUserInfo());
        $this->assertSame('http://user:pass@example.com', (string)$new);
    }

    public function testWithHost(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withHost('example.org');

        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('example.org', $new->getHost());
        $this->assertSame('http://example.org', (string)$new);
    }

    public function testWithPort(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withPort(8080);

        $this->assertNull($uri->getPort());
        $this->assertSame(8080, $new->getPort());
        $this->assertSame('http://example.com:8080', (string)$new);
    }

    public function testWithInvalidPort(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = new Uri('http://example.com');
        $uri->withPort(100000); // Port out of range
    }

    public function testWithPath(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withPath('/test/path');

        $this->assertSame('', $uri->getPath());
        $this->assertSame('/test/path', $new->getPath());
        $this->assertSame('http://example.com/test/path', (string)$new);
    }

    public function testWithInvalidPath(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $uri = new Uri('http://example.com');
        $uri->withPath('/test?invalid'); // Path cannot contain query
    }

    public function testWithQuery(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withQuery('q=test&x=y');

        $this->assertSame('', $uri->getQuery());
        $this->assertSame('q=test&x=y', $new->getQuery());
        $this->assertSame('http://example.com?q=test&x=y', (string)$new);
    }

    public function testWithFragment(): void
    {
        $uri = new Uri('http://example.com');
        $new = $uri->withFragment('section1');

        $this->assertSame('', $uri->getFragment());
        $this->assertSame('section1', $new->getFragment());
        $this->assertSame('http://example.com#section1', (string)$new);
    }
}
