<?php

namespace Isocontent\Tests\Parser;

use Isocontent\AST\Builder;
use Isocontent\Exception\UnsupportedFormatException;
use Isocontent\Parser\ArrayParser;
use PHPUnit\Framework\TestCase;

final class ArrayParserTest extends TestCase
{
    private ArrayParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ArrayParser();
    }

    public function testItSupportsArrayFormat(): void
    {
        $this->assertTrue($this->parser->supportsFormat('array'));
        $this->assertFalse($this->parser->supportsFormat('list'));
    }

    public function testItParsesTextNode(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addTextNode')->with('Hello, World!');

        $this->parser->parse($builder, ['type' => 'text', 'value' => 'Hello, World!']);
    }

    public function testItParsesBlockNode(): void
    {
        $builder = $this->createMock(Builder::class);
        $childBuilder = $this->createMock(Builder::class);

        $builder
            ->expects($this->once())
            ->method('addBlockNode')
            ->with('title', ['level' => '1'])
            ->willReturn($childBuilder);

        $this->parser->parse($builder, [
            [
                'type' => 'block',
                'block_type' => 'title',
                'arguments' => ['level' => '1'],
            ],
        ]);
    }

    public function testItParsesNodeList(): void
    {
        $builder = $this->createMock(Builder::class);
        $childBuilder = $this->createMock(Builder::class);

        $builder
            ->expects($this->once())
            ->method('addTextNode')
            ->with('First node');
        $builder
            ->expects($this->once())
            ->method('addBlockNode')
            ->with('paragraph')
            ->willReturn($childBuilder);

        $this->parser->parse($builder, [
            ['type' => 'text', 'value' => 'First node'],
            ['type' => 'block', 'block_type' => 'paragraph'],
        ]);
    }

    public function testItParsesBlockNodeWithChildren(): void
    {
        $builder = $this->createMock(Builder::class);
        $childBuilder = $this->createMock(Builder::class);

        $builder
            ->expects($this->once())
            ->method('addBlockNode')
            ->with('section', [])
            ->willReturn($childBuilder);

        $childBuilder
            ->expects($this->once())
            ->method('addTextNode')
            ->with('Child text');

        $this->parser->parse($builder, [
            [
                'type' => 'block',
                'block_type' => 'section',
                'arguments' => [],
                'children' => [
                    ['type' => 'text', 'value' => 'Child text'],
                ],
            ],
        ]);
    }

    public function testItThrowsIfInputIsNotAnArray(): void
    {
        $this->expectException(UnsupportedFormatException::class);

        $builder = $this->createMock(Builder::class);
        $this->parser->parse($builder, 'not an array');
    }
}
