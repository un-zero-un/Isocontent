<?php

namespace Isocontent\Tests\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Renderer\TextDebugRenderer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TextDebugRendererTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_supports_text(): void
    {
        $this->assertTrue((new TextDebugRenderer)->supportsFormat('text_debug'));
    }

    public function test_it_does_not_supports_non_text(): void
    {
        $this->assertFalse((new TextDebugRenderer)->supportsFormat('html'));
        $this->assertFalse((new TextDebugRenderer)->supportsFormat('json'));
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function test_it_renders_ast_to_text(NodeList $ast, string $expectedOutput): void
    {
        $this->assertSame(
            $expectedOutput,
            (new TextDebugRenderer)->render($ast)
        );
    }

    public function test_it_returns_only_node_type_with_dummy_node(): void
    {
        $node = $this->prophesize(Node::class);
        $node->getType()->shouldBeCalled()->willReturn('dummy');

        $this->assertSame(
            "# dummy\n",
            (new TextDebugRenderer)->render(
                NodeList::fromArray([$node->reveal()])
            )
        );
    }

    public function renderDataProvider()
    {
        return [
            [
                NodeList::fromArray([TextNode::fromText('foobar')]),
                "# text(foobar)\n",
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar')
                    ])),
                ]),
                "# block(type=inline_text)\n" .
                "  # text(foobar)\n",
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar')
                    ])),
                    BlockNode::fromBlockType('strong', [], NodeList::fromArray([
                        TextNode::fromText('bazqux')
                    ])),
                ]),
                "# block(type=inline_text)\n" .
                "  # text(foobar)\n" .
                "# block(type=strong)\n" .
                "  # text(bazqux)\n",
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar')
                    ])),
                    BlockNode::fromBlockType('generic')
                ]),
                "# block(type=inline_text)\n" .
                "  # text(foobar)\n" .
                "# block(type=generic)\n",
            ]
        ];
    }
}
