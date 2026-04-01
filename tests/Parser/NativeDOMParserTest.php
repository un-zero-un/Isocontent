<?php

namespace Isocontent\Tests\Parser;

use Isocontent\AST\Builder;
use Isocontent\Exception\UnsupportedFormatException;
use Isocontent\Parser\NativeDOMParser;
use PHPUnit\Framework\TestCase;

final class NativeDOMParserTest extends TestCase
{
    protected NativeDOMParser $parser;

    protected function setUp(): void
    {
        $this->parser = new NativeDOMParser();

        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped('The NativeDOMParser requires PHP 8.4 or higher.');
        }
    }

    public function testItSupportsHTML(): void
    {
        $this->assertTrue($this->parser->supportsFormat('html'));
        $this->assertFalse($this->parser->supportsFormat('xml'));
    }

    public function testItDoesNotSupportOtherFormatThanHtml(): void
    {
        $this->assertFalse($this->parser->supportsFormat('txt'));
        $this->assertFalse($this->parser->supportsFormat('htm'));
        $this->assertFalse($this->parser->supportsFormat('json'));
    }

    public function testItParsesSimpleHtml(): void
    {
        $input = '<p><span>Paragraph text</span></p>';

        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $spanBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('inline_text', [])->willReturn($spanBuilder);
        $spanBuilder->expects($this->once())->method('addTextNode')->with('Paragraph text')->willReturn($spanBuilder);

        (new NativeDOMParser())->parse($builder, $input);
    }

    public function testItParsesSimpleHtmlWithComment(): void
    {
        $input = '<p><span>Paragraph text<!-- And a comment --></span></p>';

        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $spanBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('inline_text', [])->willReturn($spanBuilder);
        $spanBuilder->expects($this->once())->method('addTextNode')->with('Paragraph text')->willReturn($spanBuilder);

        (new NativeDOMParser())->parse($builder, $input);
    }

    public function testItParsesEmptyNode(): void
    {
        $input = '<p></p>';
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);

        (new NativeDOMParser())->parse($builder, $input);
    }

    public function testItParsesLinkWithAttribute(): void
    {
        $input = '<p><a href="https://example.com">Example</a></p>';

        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $aBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('link', ['href' => 'https://example.com'])->willReturn($aBuilder);
        $aBuilder->expects($this->once())->method('addTextNode')->with('Example')->willReturn($aBuilder);

        (new NativeDOMParser())->parse($builder, $input);
    }

    public function testItParsesLinkBooleanAttribute(): void
    {
        $input = '<p><a download>Example</a></p>';

        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $aBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('link', ['download' => true])->willReturn($aBuilder);
        $aBuilder->expects($this->once())->method('addTextNode')->with('Example')->willReturn($aBuilder);

        (new NativeDOMParser())->parse($builder, $input);
    }

    public function testItParsesUnknownNodeType(): void
    {
        $input = '<custom>Some custom content</custom>';

        $builder = $this->createMock(Builder::class);
        $genericBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('generic', [])->willReturn($genericBuilder);
        $genericBuilder->expects($this->once())->method('addTextNode')->with('Some custom content')->willReturn($genericBuilder);

        (new NativeDOMParser())->parse($builder, $input);
    }

    public function testItDoesNotThrowErrorWithEmptyHtml(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->expects($this->never())->method('addTextNode');
        $builder->expects($this->never())->method('addBlockNode');

        (new NativeDOMParser())->parse($builder, '');
    }

    public function testItThrowsIfInputIsNotAString(): void
    {
        $this->expectException(UnsupportedFormatException::class);

        $builder = $this->createMock(Builder::class);

        (new NativeDOMParser())->parse($builder, 123);
    }
}
