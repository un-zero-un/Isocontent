<?php

namespace Isocontent\Tests\Bridge\Symfony\Bundle\Extension\Twig;

use Isocontent\AST\BlockNode;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Bridge\Symfony\Bundle\Extension\Twig\IsoContentExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class IsoContentExtensionTest extends TestCase
{
    /**
     * @dataProvider astProvider
     */
    public function tes_display_iso_content_function(NodeList $ast, string $expect)
    {
        $extension = new IsoContentExtension();
        $this->assertEquals($expect, $extension->displayIsoContent($ast));
    }

    /**
     * @dataProvider functionProvider
     */
    public function test_get_twig_functions(string $expect, TwigFunction $function)
    {
        $this->assertSame($expect, $function->getName());
    }

    public function test_get_function()
    {
        $extension = new IsoContentExtension();
        $this->assertTrue(is_array($extension->getFunctions()));
        $functions = $extension->getFunctions();
        $this->assertInstanceOf(TwigFunction::class, $functions[0]);
    }

    public static function astProvider()
    {
        return [
            [
                NodeList::fromArray([TextNode::fromText('Foo')]),
                'Foo',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                        TextNode::fromText('Foo')
                    ])),
                ]),
                '<span>Foo</span>',
            ],
            [
                NodeList::fromArray([
                    BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                        TextNode::fromText('Some paragraph '),
                        BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                            TextNode::fromText('with some inline text'),
                        ])),
                        BlockNode::fromBlockType('list', ['ordered' => false], NodeList::fromArray([
                            BlockNode::fromBlockType('list_item', [], NodeList::fromArray([
                                TextNode::fromText('Unsorted'),
                            ])),
                            BlockNode::fromBlockType('list_item', [], NodeList::fromArray([
                                TextNode::fromText('data'),
                            ]))
                        ]))
                    ])),
                ]),
                '<p>Some paragraph <span>with some inline text</span><ul><li>Unsorted</li><li>data</li></ul></p>'
            ],
            [
                NodeList::fromArray([]),
                ''
            ],

        ];
    }

    public function functionProvider()
    {
        $extension = new IsoContentExtension();
        $function = $extension->getFunctions();
        return [
            ['display_isocontent', $function[0]],
        ];
    }
}
