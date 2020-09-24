<?php

declare(strict_types=1);

namespace Isocontent\Tests\E2E;

use Isocontent\Isocontent;
use Isocontent\Parser\ArrayParser;
use Isocontent\Renderer\HTMLRenderer;
use PHPUnit\Framework\TestCase;

class ParseArrayTest extends TestCase
{
    /**
     * @var Isocontent
     */
    private $isocontent;

    public function setUp(): void
    {
        $this->isocontent = new Isocontent([new ArrayParser], [new HTMLRenderer]);

        parent::setUp();
    }

    /**
     * @dataProvider dataProvider
     */
    public function test_output_renders_as_html(string $htmlInput, array $expectedOutput): void
    {
        $this->assertEquals($htmlInput, $this->isocontent->render($this->isocontent->buildAST($expectedOutput, 'array'), 'html'));
    }

    public function dataProvider(): array
    {
        return [
            [
                '<p>Foo</p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]]
            ],
            [
                '<span>Foo</span>',
                [[
                    'type' => 'block',
                    'block_type' => 'inline_text',
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]]
            ],
            [
                '<span>Foo</span>',
                [[
                    'type' => 'block',
                    'block_type' => 'inline_text',
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]]
            ],
            [
                '<strong>Foo <span /></strong>',
                [[
                    'type' => 'block',
                    'block_type' => 'strong',
                    'children' => [
                        ['type' => 'text', 'value' => 'Foo '],
                        ['type' => 'block', 'block_type' => 'generic'],
                    ],
                ]]
            ],
            [
                '<p><span>Foo</span></p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'children' => [[
                        'type' => 'block',
                        'block_type' => 'inline_text',
                        'children' => [['type' => 'text', 'value' => 'Foo']],
                    ]],
                ]]
            ],
            [
                '<p><span>Foo</span> <span>Bar</span> <span>Baz</span></p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'children' => [
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'children' => [['type' => 'text', 'value' => 'Foo']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'children' => [['type' => 'text', 'value' => 'Bar']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'children' => [['type' => 'text', 'value' => 'Baz']],
                        ],
                    ],
                ]]
            ],
            [
                '<p> <span>Foo</span> <span>Bar</span> <span>Baz</span> </p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'children' => [
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'children' => [['type' => 'text', 'value' => 'Foo']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'children' => [['type' => 'text', 'value' => 'Bar']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'children' => [['type' => 'text', 'value' => 'Baz']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                    ],
                ]]
            ],
            [
                '<p> <span>Foo</span> <span><em>Emphasis</em> text</span> <span>Baz</span> </p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'children' => [
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'children' => [['type' => 'text', 'value' => 'Foo']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'children' => [
                                [
                                    'type' => 'block',
                                    'block_type' => 'emphasis',
                                    'children' => [['type' => 'text', 'value' => 'Emphasis']],
                                ],
                                ['type' => 'text', 'value' => ' text'],
                            ],
                        ],
                        ['type' => 'text', 'value' => ' '],
                        [
                            'type' => 'block',
                            'block_type' => 'inline_text',
                            'children' => [['type' => 'text', 'value' => 'Baz']],
                        ],
                        ['type' => 'text', 'value' => ' '],
                    ],
                ]]
            ],
            [
                '<h4>Foo</h4> <p> <span><em>Emphasis</em> text</span> </p> <ul><li>Baz</li></ul><ol><li>Qux</li></ol>',
                [
                    [
                        'type' => 'block',
                        'block_type' => 'title',
                        'level' => 4,
                        'children' => [['type' => 'text', 'value' => 'Foo']],
                    ],
                    ['type' => 'text', 'value' => ' '],
                    [
                        'type' => 'block',
                        'block_type' => 'paragraph',
                        'children' => [
                            ['type' => 'text', 'value' => ' '],
                            [
                                'type' => 'block',
                                'block_type' => 'inline_text',
                                'children' => [
                                    [
                                        'type' => 'block',
                                        'block_type' => 'emphasis',
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
                        'ordered' => false,
                        'children' => [
                            [
                                'type' => 'block',
                                'block_type' => 'list_item',
                                'children' => [
                                    ['type' => 'text', 'value' => 'Baz'],
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'block',
                        'block_type' => 'list',
                        'ordered' => true,
                        'children' => [
                            [
                                'type' => 'block',
                                'block_type' => 'list_item',
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
