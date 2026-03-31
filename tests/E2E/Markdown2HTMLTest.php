<?php

namespace Isocontent\Tests\E2E;

use Isocontent\AST\Builder;
use Isocontent\Parser\CommonMarkParser;
use Isocontent\Renderer\HTMLRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Markdown2HTMLTest extends TestCase
{
    #[DataProvider('markdownProvider')]
    public function testItConvertsMarkdownToHtml(string $markdown, string $expectedHtml): void
    {
        $builder = Builder::create();
        (new CommonMarkParser())->parse($builder, $markdown);
        $this->assertSame($expectedHtml, (new HTMLRenderer())->render($builder->getAST()));
    }

    public static function markdownProvider(): array
    {
        return [
            ['**bold**', '<p><strong>bold</strong></p>'],
            ['*emphasis*', '<p><em>emphasis</em></p>'],
            ['~~deleted~~', '<p><del>deleted</del></p>'],
            ['`code`', '<p><code>code</code></p>'],
            ['[click](https://example.com)', '<p><a>click</a></p>'],
            ['## Heading', '<h2>Heading</h2>'],
            ['- foo', '<ul><li>foo</li></ul>'],
            ['1. foo', '<ol><li>foo</li></ol>'],
            ['> quote', '<blockquote>quote</blockquote>'],
            ['---', '<hr />'],
            [
                "# Title\n\nA paragraph with **bold** and *emphasis*.\n\n- one\n- two",
                '<h1>Title</h1>'
                .'<p>A paragraph with <strong>bold</strong> and <em>emphasis</em>.</p>'
                .'<ul><li>one</li><li>two</li></ul>',
            ],
        ];
    }
}
