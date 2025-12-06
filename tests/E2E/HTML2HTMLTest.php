<?php

namespace Isocontent\Tests\E2E;

use Isocontent\AST\Builder;
use Isocontent\Parser\ArrayParser;
use Isocontent\Parser\DOMParser;
use Isocontent\Parser\NativeDOMParser;
use Isocontent\Renderer\HTMLRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HTML2HTMLTest extends TestCase
{
    #[DataProvider('htmlProvider')]
    public function testItConvertsHtmlBackToHtmlWithLegacyDomParser(string $html)
    {
        $builder = Builder::create();
        (new DOMParser())->parse($builder, $html);
        $this->assertSame($html, (new HTMLRenderer())->render($builder->getAST()));
    }

    #[DataProvider('htmlProvider')]
    public function testItConvertsHtmlBackToHtml(string $html)
    {
        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped('The NativeDOMParser requires PHP 8.4 or higher.');
        }

        $builder = Builder::create();
        (new NativeDOMParser())->parse($builder, $html);
        $this->assertSame($html, (new HTMLRenderer())->render($builder->getAST()));
    }

    #[DataProvider('htmlProvider')]
    public function testItConvertsHtmlBackToHtmlViaArrays(string $html)
    {
        $builder = Builder::create();
        (new DOMParser())->parse($builder, $html);

        $arrayBuilder = Builder::create();
        (new ArrayParser())->parse($arrayBuilder, $builder->getAST()->toArray());

        $this->assertSame($html, (new HTMLRenderer())->render($arrayBuilder->getAST()));
    }

    public static function htmlProvider(): array
    {
        return [
            ['<strong>foobar</strong>'],
            ['<span><strong>foobar</strong></span>'],
            ['<span><strong>foobar</strong><strong>bazqux</strong></span>'],
            [
                '<h1>Heading level 1</h1>'
                .'<h2>Heading level 2</h2>'
                .'<h3>Heading level 3</h3>'
                .'<h4>Heading level 4</h4>'
                .'<h5>Heading level 5</h5>'
                .'<h6>Heading level 6</h6>'
                .'<p>Some text with <strong>strong</strong> and <em>emphasis</em></p>'
                .'<hr />'
                .'<p>Now with <del>del</del> and even <sub>3</sub><sup>4</sup></p>'
                .'<p>Inline&nbsp;<code>code</code> ? Yup.</p>',
            ],
            [
                '<ul><li>foo</li><li>bar</li><li>baz <ol><li>qux</li></ol></li></ul>'
                .'<blockquote><p>Foobar</p></blockquote><br />',
            ],
        ];
    }
}
