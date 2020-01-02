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
    public function test_it_supports_json(): void
    {
        $this->assertTrue((new JSONRenderer)->supportsFormat('json'));
    }

    public function test_it_does_not_supports_non_json(): void
    {
        $this->assertFalse((new JSONRenderer)->supportsFormat('html'));
        $this->assertFalse((new JSONRenderer)->supportsFormat('js'));
    }

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
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('foobar')
                    ])),
                ]),
                '[{"type":"block","block_type":"inline_text","children":[{"type":"text","value":"foobar"}]}]',
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
                '[{"type":"block","block_type":"inline_text","children":[{"type":"text","value":"foobar"}]},' .
                '{"type":"block","block_type":"strong","children":[{"type":"text","value":"bazqux"}]}]',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('title', ['level' => 4], NodeList::fromArray([
                        TextNode::fromText('level 4 title'),
                    ])),
                    BlockNode::fromBlockType('quote', [], NodeList::fromArray([
                        TextNode::fromText('Quoted item'),

                    ])),

                    BlockNode::fromBlockType('title', ['level' => 5], NodeList::fromArray([
                        TextNode::fromText('level 5 title'),
                    ])),
                    TextNode::fromText('followed by an'),
                    BlockNode::fromBlockType('new_line', [], null),
                    BlockNode::fromBlockType('emphasis', [], NodeList::fromArray([
                        TextNode::fromText('emphasis'),
                    ])),
                    TextNode::fromText('and'),
                    BlockNode::fromBlockType('strong', [], NodeList::fromArray([
                        TextNode::fromText('Strong text'),
                    ])),
                    TextNode::fromText('followed by'),
                    BlockNode::fromBlockType('list', ['ordered' => false], NodeList::fromArray([
                        BlockNode::fromBlockType('list_item', ['ordered' => false], NodeList::fromArray([
                            TextNode::fromText('a list'),
                        ])), BlockNode::fromBlockType('list_item', ['ordered' => false], NodeList::fromArray([
                            TextNode::fromText('of item'),
                        ]))
                    ])),
                ]),
                '[{"level":4,"type":"block","block_type":"title","children":[{"type":"text","value":"level 4 title"}]},' .
                '{"type":"block","block_type":"quote","children":[{"type":"text","value":"Quoted item"}]},' .
                '{"level":5,"type":"block","block_type":"title","children":[{"type":"text","value":"level 5 title"}]},' .
                '{"type":"text","value":"followed by an"},' .
                '{"type":"block","block_type":"new_line"},' .
                '{"type":"block","block_type":"emphasis","children":[{"type":"text","value":"emphasis"}]},' .
                '{"type":"text","value":"and"},' .
                '{"type":"block","block_type":"strong","children":[{"type":"text","value":"Strong text"}]},' .
                '{"type":"text","value":"followed by"},' .
                '{"ordered":false,"type":"block","block_type":"list","children":[{"ordered":false,"type":"block","block_type":"list_item","children":[{"type":"text","value":"a list"}]},' .
                '{"ordered":false,"type":"block","block_type":"list_item","children":[{"type":"text","value":"of item"}]}]}]',
            ],
        ];
    }
}
