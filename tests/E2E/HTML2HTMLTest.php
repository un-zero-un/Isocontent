<?php

namespace Isocontent\Tests\E2E;

use Isocontent\AST\Builder;
use Isocontent\Parser\ArrayParser;
use Isocontent\Parser\DOMParser;
use Isocontent\Renderer\HTMLRenderer;
use PHPUnit\Framework\TestCase;

class HTML2HTMLTest extends TestCase
{
    /**
     * @dataProvider htmlProvider
     */
    public function test_it_converts_html_back_to_html(string $html)
    {
        $builder = Builder::create();
        (new DOMParser)->parse($builder, $html);
        $this->assertSame($html, (new HTMLRenderer)->render($builder->getAST()));
    }
    /**
     * @dataProvider htmlProvider
     */
    public function test_it_converts_html_back_to_html_via_arrays(string $html)
    {
        $builder = Builder::create();
        (new DOMParser)->parse($builder, $html);

        $arrayBuilder = Builder::create();
        (new ArrayParser)->parse($arrayBuilder, $builder->getAST()->toArray());

        $this->assertSame($html, (new HTMLRenderer)->render($arrayBuilder->getAST()));
    }

    public function htmlProvider(): array
    {
        return [
            ['<strong>foobar</strong>'],
            ['<span><strong>foobar</strong></span>'],
            ['<span><strong>foobar</strong><strong>bazqux</strong></span>'],
            [
                '<h1>Heading level 1</h1>' .
                '<h2>Heading level 2</h2>' .
                '<h3>Heading level 3</h3>' .
                '<h4>Heading level 4</h4>' .
                '<h5>Heading level 5</h5>' .
                '<h6>Heading level 6</h6>' .
                '<p>Some text with <strong>strong</strong> and <em>emphasis</em></p>'
            ],
            [
                '<ul><li>foo</li><li>bar</li><li>baz <ol><li>qux</li></ol></li></ul>' .
                '<blockquote><p>Foobar</p></blockquote><br />'
            ]
        ];
    }
}
