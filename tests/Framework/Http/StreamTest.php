<?php

declare(strict_types=1);

namespace Tests\Framework\Http;

use App\Framework\Http\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use InvalidArgumentException;

class StreamTest extends TestCase
{
    public function testConstructorWithString(): void
    {
        $stream = new Stream('Hello, World!');
        
        $this->assertSame(13, $stream->getSize());
        $this->assertSame('Hello, World!', (string)$stream);
    }
    
    public function testConstructorWithResource(): void
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, 'Resource content');
        fseek($resource, 0);
        
        $stream = new Stream($resource);
        
        $this->assertSame('Resource content', (string)$stream);
    }
    
    public function testConstructorWithInvalidArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        new Stream(123); // Not a string or resource
    }
    
    public function testToString(): void
    {
        $stream = new Stream('Stream content');
        
        $this->assertSame('Stream content', (string)$stream);
    }
    
    public function testClose(): void
    {
        $stream = new Stream('test');
        $stream->close();
        
        $this->expectException(RuntimeException::class);
        $stream->getContents();
    }
    
    public function testDetach(): void
    {
        $stream = new Stream('test');
        $resource = $stream->detach();
        
        $this->assertIsResource($resource);
        
        $this->expectException(RuntimeException::class);
        $stream->getContents();
    }
    
    public function testGetSize(): void
    {
        $stream = new Stream('12345');
        
        $this->assertSame(5, $stream->getSize());
    }
    
    public function testTell(): void
    {
        $stream = new Stream('test');
        
        $this->assertSame(0, $stream->tell());
        
        $stream->read(2);
        $this->assertSame(2, $stream->tell());
    }
    
    public function testTellWithDetachedStream(): void
    {
        $stream = new Stream('test');
        $stream->detach();
        
        $this->expectException(RuntimeException::class);
        $stream->tell();
    }
    
    public function testEof(): void
    {
        $stream = new Stream('test');
        
        $this->assertFalse($stream->eof());
        
        $stream->read(10); // Read beyond the end
        $this->assertTrue($stream->eof());
    }
    
    public function testIsSeekable(): void
    {
        $stream = new Stream('test');
        
        $this->assertTrue($stream->isSeekable());
        
        $stream->detach();
        $this->assertFalse($stream->isSeekable());
    }
    
    public function testSeek(): void
    {
        $stream = new Stream('test');
        
        $stream->seek(2);
        $this->assertSame(2, $stream->tell());
        
        $stream->seek(0);
        $this->assertSame(0, $stream->tell());
    }
    
    public function testSeekWithDetachedStream(): void
    {
        $stream = new Stream('test');
        $stream->detach();
        
        $this->expectException(RuntimeException::class);
        $stream->seek(2);
    }
    
    public function testRewind(): void
    {
        $stream = new Stream('test');
        
        $stream->seek(2);
        $this->assertSame(2, $stream->tell());
        
        $stream->rewind();
        $this->assertSame(0, $stream->tell());
    }
    
    public function testIsWritable(): void
    {
        $stream = new Stream('test');
        
        $this->assertTrue($stream->isWritable());
        
        $stream->detach();
        $this->assertFalse($stream->isWritable());
    }
    
    public function testWrite(): void
    {
        $stream = new Stream('test');
        
        $this->assertSame(6, $stream->write(' data'));
        $stream->rewind();
        $this->assertSame('test data', (string)$stream);
    }
    
    public function testWriteWithDetachedStream(): void
    {
        $stream = new Stream('test');
        $stream->detach();
        
        $this->expectException(RuntimeException::class);
        $stream->write('data');
    }
    
    public function testIsReadable(): void
    {
        $stream = new Stream('test');
        
        $this->assertTrue($stream->isReadable());
        
        $stream->detach();
        $this->assertFalse($stream->isReadable());
    }
    
    public function testRead(): void
    {
        $stream = new Stream('test data');
        
        $this->assertSame('test', $stream->read(4));
        $this->assertSame(' dat', $stream->read(4));
        $this->assertSame('a', $stream->read(4)); // Only one character left
    }
    
    public function testReadWithDetachedStream(): void
    {
        $stream = new Stream('test');
        $stream->detach();
        
        $this->expectException(RuntimeException::class);
        $stream->read(4);
    }
    
    public function testGetContents(): void
    {
        $stream = new Stream('test data');
        
        $this->assertSame('test data', $stream->getContents());
    }
    
    public function testGetContentsAfterReading(): void
    {
        $stream = new Stream('test data');
        
        $stream->read(5); // Read 'test '
        $this->assertSame('data', $stream->getContents());
    }
    
    public function testGetContentsWithDetachedStream(): void
    {
        $stream = new Stream('test');
        $stream->detach();
        
        $this->expectException(RuntimeException::class);
        $stream->getContents();
    }
    
    public function testGetMetadata(): void
    {
        $stream = new Stream('test');
        
        $metadata = $stream->getMetadata();
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('seekable', $metadata);
        
        $seekable = $stream->getMetadata('seekable');
        $this->assertTrue($seekable);
        
        // Non-existent key
        $this->assertNull($stream->getMetadata('non_existent'));
    }
    
    public function testGetMetadataWithDetachedStream(): void
    {
        $stream = new Stream('test');
        $stream->detach();
        
        $this->assertSame([], $stream->getMetadata());
        $this->assertNull($stream->getMetadata('seekable'));
    }
}
