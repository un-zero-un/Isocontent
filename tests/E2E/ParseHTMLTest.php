<?php

declare(strict_types=1);

namespace Isocontent\Tests\E2E;

use Isocontent\Isocontent;
use Isocontent\Parser\ArrayParser;
use Isocontent\Parser\DOMParser;
use Isocontent\Renderer\HTMLRenderer;
use PHPUnit\Framework\TestCase;

class ParseHTMLTest extends TestCase
{
    /**
     * @var Isocontent
     */
    private $isocontent;

    public function setUp(): void
    {
        $this->isocontent = new Isocontent([new DOMParser, new ArrayParser], [new HTMLRenderer]);

        parent::setUp();
    }

    /**
     * @dataProvider dataProvider
     */
    public function test_output_is_conform_to_html(string $htmlInput, array $expectedOutput): void
    {
        $this->assertEquals($expectedOutput, $this->isocontent->buildAST($htmlInput, 'html')->toArray());
    }

    public function dataProvider(): array
    {
        return [
            [
                'Foo',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]]
            ],
            [
                '<span>Foo</span>',
                [[
                    'type' => 'block',
                    'block_type' => 'inline_text',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]]
            ],
            [
                '<span dir="rtl">Foo</span><!-- foobar -->',
                [[
                    'type' => 'block',
                    'block_type' => 'inline_text',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]]
            ],
            [
                '<strong>Foo <img /></strong>',
                [[
                    'type' => 'block',
                    'block_type' => 'strong',
                    'arguments' => [],
                    'children' => [
                        ['type' => 'text', 'value' => 'Foo '],
                        ['type' => 'block', 'block_type' => 'generic', 'arguments' => []],
                    ],
                ]]
            ],
            [
                '<dir>Foo</dir>',
                [[
                    'type' => 'block',
                    'block_type' => 'generic',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]]
            ],
            [
                '<p><span>Foo</span></p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'arguments' => [],
                    'children' => [[
                        'type' => 'block',
                        'block_type' => 'inline_text',
                        'arguments' => [],
                        'children' => [['type' => 'text', 'value' => 'Foo']],
                    ]],
                ]]
            ],
            [
                '<p><span>Foo</span> <span>Bar</span> <span>Baz</span></p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'arguments' => [],
                    'children' => [
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'arguments' => [],
                            'children' => [['type' => 'text', 'value' => 'Foo']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'arguments' => [],
                            'children' => [['type' => 'text', 'value' => 'Bar']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'arguments' => [],
                            'children' => [['type' => 'text', 'value' => 'Baz']],
                        ],
                    ],
                ]]
            ],
            [
                '<p>
                    <span>Foo</span>
                    <span>Bar</span>
                    <span>Baz</span>
                </p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'arguments' => [],
                    'children' => [
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'arguments' => [],
                            'children' => [['type' => 'text', 'value' => 'Foo']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'arguments' => [],
                            'children' => [['type' => 'text', 'value' => 'Bar']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'arguments' => [],
                            'children' => [['type' => 'text', 'value' => 'Baz']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                    ],
                ]]
            ],
            [
                '<p>
                    <span>Foo</span>
                    <span><em>Emphasis</em> text</span>
                    <span>Baz</span>
                </p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'arguments' => [],
                    'children' => [
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'arguments' => [],
                            'children' => [['type' => 'text', 'value' => 'Foo']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'arguments' => [],
                            'children' => [
                                [
                                    'type' => 'block',
                                    'block_type' => 'emphasis',
                                    'arguments' => [],
                                    'children' => [['type' => 'text', 'value' => 'Emphasis']],
                                ],
                                ['type' => 'text', 'value' => ' text'],
                            ],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'arguments' => [],
                            'children' => [['type' => 'text', 'value' => 'Baz']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                    ],
                ]]
            ],
            [
                '<h4>Foo</h4>
                <p>
                    <span><em>Emphasis</em> text</span>
                </p>
                <ul><li>Baz</li></ul>
                <ol><li>Qux</li></ol>',
                [
                    [
                        'type' => 'block',
                        'block_type' => 'title',
                        'arguments' => ['level' => 4],
                        'children' => [['type' => 'text', 'value' => 'Foo']],
                    ],
                    ['type' => 'text', 'value' => ' '],
                    [
                        'type' => 'block',
                        'block_type' => 'paragraph',
                        'arguments' => [],
                        'children' => [
                            ['type' => 'text', 'value' => ' '],
                            [
                                'type' => 'block',
                                'block_type' => 'inline_text',
                                'arguments' => [],
                                'children' => [
                                    [
                                        'type' => 'block',
                                        'block_type' => 'emphasis',
                                        'arguments' => [],
                                        'children' => [
                                            ['type' => 'text', 'value' => 'Emphasis'],
                                        ],
                                    ],
                                    ['type' => 'text', 'value' => ' text']
                                ],
                            ],
                            ['type' => 'text', 'value' => ' '],
                        ],
                    ],
                    ['type' => 'text', 'value' => ' '],
                    [
                        'type' => 'block',
                        'block_type' => 'list',
                        'arguments' => ['ordered' => false],
                        'children' => [
                            [
                                'type' => 'block',
                                'block_type' => 'list_item',
                                'arguments' => [],
                                'children' => [
                                    ['type' => 'text', 'value' => 'Baz'],
                                ],
                            ],
                        ],
                    ],
                    ['type' => 'text', 'value' => ' '],
                    [
                        'type' => 'block',
                        'block_type' => 'list',
                        'arguments' => ['ordered' => true],
                        'children' => [
                            [
                                'type' => 'block',
                                'block_type' => 'list_item',
                                'arguments' => [],
                                'children' => [
                                    ['type' => 'text', 'value' => 'Qux'],
                                ],
                            ],
                        ],
                    ],
                ]
            ],
        ];
    }
}
