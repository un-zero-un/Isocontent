<?php

declare(strict_types=1);

namespace Isocontent\Tests\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Renderer\HTMLRenderer;
use PHPUnit\Framework\TestCase;

class HTMLRendererTest extends TestCase
{
    public function test_it_supports_html(): void
    {
        $this->assertTrue((new HTMLRenderer)->supportsFormat('html'));
    }

    public function test_it_does_not_supports_non_html(): void
    {
        $this->assertFalse((new HTMLRenderer)->supportsFormat('htm'));
        $this->assertFalse((new HTMLRenderer)->supportsFormat('json'));
    }

    /**
     * @dataProvider dataProvider
     */
    public function test_it_renders_ast_to_html(NodeList $ast, string $expectedOutput): void
    {
        $this->assertSame($expectedOutput, (new HTMLRenderer)->render($ast));
    }

    public function dataProvider(): array
    {
        return [
            [
                NodeList::fromArray([TextNode::fromText('foobar')]),
                'foobar',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', NodeList::fromArray([
                        TextNode::fromText('foobar')
                    ])),
                ]),
                '<span>foobar</span>',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', NodeList::fromArray([
                        TextNode::fromText('foobar')
                    ])),
                    BlockNode::fromBlockType('strong', NodeList::fromArray([
                        TextNode::fromText('bazqux')
                    ])),
                ]),
                '<span>foobar</span><strong>bazqux</strong>',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', NodeList::fromArray([
                        TextNode::fromText('foobar')
                    ])),
                    BlockNode::fromBlockType('generic')
                ]),
                '<span>foobar</span><span />',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', NodeList::fromArray([
                        TextNode::fromText('foobar')
                    ])),
                    $this->prophesize(Node::class)->reveal()
                ]),
                '<span>foobar</span>',
            ],
        ];
    }
}
