<?php

declare(strict_types=1);

namespace Isocontent\Tests\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Renderer\JSONRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JSONRendererTest extends TestCase
{
    public function testItSupportsJson(): void
    {
        $this->assertTrue((new JSONRenderer())->supportsFormat('json'));
    }

    public function testItDoesNotSupportsNonJson(): void
    {
        $this->assertFalse((new JSONRenderer())->supportsFormat('html'));
        $this->assertFalse((new JSONRenderer())->supportsFormat('js'));
    }

    #[DataProvider('dataProvider')]
    public function testItRendersAstToJson(NodeList $ast, string $expectedOutput): void
    {
        $this->assertSame($expectedOutput, (new JSONRenderer())->render($ast));
    }

    public static function dataProvider(): array
    {
        return [
            [
                NodeList::fromArray([TextNode::fromText('foobar')]),
                '[{"type":"text","value":"foobar"}]',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar'),
                    ])),
                ]),
                '[{"type":"block","block_type":"inline_text","arguments":[],"children":[{"type":"text","value":"foobar"}]}]',
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
                '[{"type":"block","block_type":"inline_text","arguments":[],"children":[{"type":"text","value":"foobar"}]},'
                .'{"type":"block","block_type":"strong","arguments":[],"children":[{"type":"text","value":"bazqux"}]}]',
            ],
        ];
    }
}
