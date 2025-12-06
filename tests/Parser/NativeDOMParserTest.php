<?php

namespace Isocontent\Tests\Parser;

use Isocontent\AST\Builder;
use Isocontent\Exception\UnsupportedFormatException;
use Isocontent\Parser\DOMParser;
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

    public function testItDoesNotThrowErrorWithEmptyHtml(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->expects($this->never())->method('addTextNode');
        $builder->expects($this->never())->method('addBlockNode');

        (new DOMParser())->parse($builder, '');
    }

    public function testItThrowsIfInputIsNotAString(): void
    {
        $this->expectException(UnsupportedFormatException::class);

        $builder = $this->createMock(Builder::class);

        (new DOMParser())->parse($builder, 123);
    }
}
