<?php

declare(strict_types=1);

namespace Isocontent\Tests\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Renderer\JSONRenderer;
use PHPUnit\Framework\TestCase;

class JSONRendererTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test_it_renders_ast_to_json(NodeList $ast, string $expectedOutput): void
    {
        $this->assertSame($expectedOutput, (new JSONRenderer)->render($ast));
    }

    public function dataProvider(): array
    {
        return [
            [
                NodeList::fromArray([TextNode::fromText('foobar')]),
                '[{"type":"text","value":"foobar"}]',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', NodeList::fromArray([
                        TextNode::fromText('foobar')
                    ])),
                ]),
                '[{"type":"block","block_type":"inline_text","children":[{"type":"text","value":"foobar"}]}]',
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
                '[{"type":"block","block_type":"inline_text","children":[{"type":"text","value":"foobar"}]},' .
                '{"type":"block","block_type":"strong","children":[{"type":"text","value":"bazqux"}]}]',
            ],
        ];
    }
}
