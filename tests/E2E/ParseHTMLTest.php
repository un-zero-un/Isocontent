<?php

declare(strict_types=1);

namespace Isocontent\Tests\E2E;

use Isocontent\Isocontent;
use Isocontent\Parser\ArrayParser;
use Isocontent\Parser\DOMParser;
use Isocontent\Parser\NativeDOMParser;
use Isocontent\Renderer\HTMLRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ParseHTMLTest extends TestCase
{
    private ?Isocontent $legacyIsocontent = null;
    private ?Isocontent $nativeIsocontent = null;

    public function setUp(): void
    {
        $this->legacyIsocontent = new Isocontent([new DOMParser(), new ArrayParser()], [new HTMLRenderer()]);

        if (PHP_VERSION_ID >= 80400) {
            $this->nativeIsocontent = new Isocontent([new NativeDOMParser(), new ArrayParser()], [new HTMLRenderer()]);
        }

        parent::setUp();
    }

    #[DataProvider('dataProvider')]
    public function testLegacyDOMParserOutputIsConformToHtml(string $htmlInput, array $expectedOutput): void
    {
        $this->assertEquals($expectedOutput, $this->legacyIsocontent->buildAST($htmlInput, 'html')->toArray());
    }

    #[DataProvider('dataProvider')]
    public function testNativeDOMParserOutputIsConformToHtml(string $htmlInput, array $expectedOutput): void
    {
        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped('The NativeDOMParser requires PHP 8.4 or higher.');
        }

        $this->assertEquals($expectedOutput, $this->nativeIsocontent->buildAST($htmlInput, 'html')->toArray());
    }

    #[DataProvider('legacyDOMParserDataProvider')]
    public function testLegacyDOMParserSpecificBehavior(string $htmlInput, array $expectedOutput): void
    {
        $this->assertEquals($expectedOutput, $this->legacyIsocontent->buildAST($htmlInput, 'html')->toArray());
    }

    public static function legacyDOMParserDataProvider(): array
    {
        return [
            'bare text is auto-wrapped in paragraph' => [
                'Foo',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]],
            ],
            'XML namespace processing instruction' => [
                '<?xml:namespace prefix = "o" /><o:p>Test</o:p>',
                [[
                    'type' => 'block',
                    'block_type' => 'paragraph',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Test']],
                ]],
            ],
        ];
    }

    public static function dataProvider(): array
    {
        return [
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
                '<a href="/toto.html">Foo</a>',
                [[
                    'type' => 'block',
                    'block_type' => 'link',
                    'arguments' => ['href' => '/toto.html'],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]],
            ],
            [
                '<a>Foo</a>',
                [[
                    'type' => 'block',
                    'block_type' => 'link',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]],
            ],
            [
                '<span dir="rtl">Foo</span><!-- foobar -->',
                [[
                    'type' => 'block',
                    'block_type' => 'inline_text',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
                ]],
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
                ]],
            ],
            [
                '<dir>Foo</dir>',
                [[
                    'type' => 'block',
                    'block_type' => 'generic',
                    'arguments' => [],
                    'children' => [['type' => 'text', 'value' => 'Foo']],
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
                ]],
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
                ]],
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
                ],
            ],
        ];
    }
}
