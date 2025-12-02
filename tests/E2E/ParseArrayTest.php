<?php

declare(strict_types=1);

namespace Isocontent\Tests\E2E;

use Isocontent\Isocontent;
use Isocontent\Parser\ArrayParser;
use Isocontent\Renderer\HTMLRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ParseArrayTest extends TestCase
{
    private ?Isocontent $isocontent = null;

    public function setUp(): void
    {
        $this->isocontent = new Isocontent([new ArrayParser()], [new HTMLRenderer()]);
    }

    #[DataProvider('dataProvider')]
    public function testOutputRendersAsHtml(string $htmlInput, array $expectedOutput): void
    {
        $this->assertEquals($htmlInput, $this->isocontent->render($this->isocontent->buildAST($expectedOutput, 'array'), 'html'));
    }

    public static function dataProvider(): array
    {
        return [
            [
                '<p>Foo</p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]],
            ],
            [
                '<span>Foo</span>',
                [[
                    'type' => 'block',
                    'block_type' => 'inline_text',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]],
            ],
            [
                '<span>Foo</span>',
                [[
                    'type' => 'block',
                    'block_type' => 'inline_text',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]],
            ],
            [
                '<strong>Foo <span /></strong>',
                [[
                    'type' => 'block',
                    'block_type' => 'strong',
                    'arguments' => [],
                    'children' => [
                        ['type' => 'text', 'value' => 'Foo '],
                        ['type' => 'block', 'block_type' => 'generic', 'arguments' => []],
                    ],
                ]],
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
                ]],
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
                ]],
            ],
            [
                '<p> <span>Foo</span> <span>Bar</span> <span>Baz</span> </p>',
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
                ]],
            ],
            [
                '<p> <span>Foo</span> <span><em>Emphasis</em> text</span> <span>Baz</span> </p>',
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
                ]],
            ],
            [
                '<h4>Foo</h4> <p> <span><em>Emphasis</em> text</span> </p> <ul><li>Baz</li></ul><ol><li>Qux</li></ol>',
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
                                    ['type' => 'text', 'value' => ' text'],
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
                ],
            ],
        ];
    }
}
