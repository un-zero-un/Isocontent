<?php

declare(strict_types=1);

namespace Isocontent\Tests\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Renderer\HTMLRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class HTMLRendererTest extends TestCase
{
    use ProphecyTrait;

    public function testItSupportsHtml(): void
    {
        $this->assertTrue((new HTMLRenderer())->supportsFormat('html'));
    }

    public function testItDoesNotSupportsNonHtml(): void
    {
        $this->assertFalse((new HTMLRenderer())->supportsFormat('htm'));
        $this->assertFalse((new HTMLRenderer())->supportsFormat('json'));
    }

    #[DataProvider('dataProvider')]
    public function testItRendersAstToHtml(NodeList $ast, string $expectedOutput): void
    {
        $this->assertSame($expectedOutput, (new HTMLRenderer())->render($ast));
    }

    public static function dataProvider(): array
    {
        return [
            [
                NodeList::fromArray([TextNode::fromText('foobar')]),
                'foobar',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar'),
                    ])),
                ]),
                '<span>foobar</span>',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar'),
                    ])),
                    BlockNode::fromBlockType('strong', [], NodeList::fromArray([
                        TextNode::fromText('bazqux'),
                    ])),
                ]),
                '<span>foobar</span><strong>bazqux</strong>',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar'),
                    ])),
                    BlockNode::fromBlockType('generic'),
                ]),
                '<span>foobar</span><span />',
            ],
        ];
    }
}
