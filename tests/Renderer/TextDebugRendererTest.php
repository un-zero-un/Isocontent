<?php

namespace Isocontent\Tests\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Renderer\TextDebugRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TextDebugRendererTest extends TestCase
{
    use ProphecyTrait;

    public function testItSupportsText(): void
    {
        $this->assertTrue((new TextDebugRenderer())->supportsFormat('text_debug'));
    }

    public function testItDoesNotSupportsNonText(): void
    {
        $this->assertFalse((new TextDebugRenderer())->supportsFormat('html'));
        $this->assertFalse((new TextDebugRenderer())->supportsFormat('json'));
    }

    #[DataProvider('renderDataProvider')]
    public function testItRendersAstToText(NodeList $ast, string $expectedOutput): void
    {
        $this->assertSame(
            $expectedOutput,
            (new TextDebugRenderer())->render($ast)
        );
    }

    public static function renderDataProvider(): iterable
    {
        return [
            [
                NodeList::fromArray([TextNode::fromText('foobar')]),
                "# text(foobar)\n",
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar'),
                    ])),
                ]),
                "# block(type=inline_text)\n"
                ."  # text(foobar)\n",
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
                "# block(type=inline_text)\n"
                ."  # text(foobar)\n"
                ."# block(type=strong)\n"
                ."  # text(bazqux)\n",
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar'),
                    ])),
                    BlockNode::fromBlockType('generic'),
                ]),
                "# block(type=inline_text)\n"
                ."  # text(foobar)\n"
                ."# block(type=generic)\n",
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('title', ['level' => '2'], NodeList::fromArray([
                        TextNode::fromText('foobar'),
                    ])),
                    BlockNode::fromBlockType('generic'),
                ]),
                "# block(level=2, type=title)\n"
                ."  # text(foobar)\n"
                ."# block(type=generic)\n",
            ],
        ];
    }

    public function testItReturnsOnlyNodeTypeWithDummyNode(): void
    {
        $node = $this->prophesize(Node::class);
        $node->getType()->shouldBeCalled()->willReturn('dummy');

        $this->assertSame(
            "# dummy\n",
            (new TextDebugRenderer())->render(
                NodeList::fromArray([$node->reveal()])
            )
        );
    }
}
