<?php

namespace Isocontent\Tests\E2E;

use Isocontent\AST\Builder;
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

    public function htmlProvider(): array
    {
        return [
            ['<strong>foobar</strong>'],
            ['<span><strong>foobar</strong></span>'],
            ['<span><strong>foobar</strong><strong>bazqux</strong></span>'],
            [
                '<h4>Heading level 1</h4>' .
                '<h5>Heading level 2</h5>' .
                '<p>Some text with <strong>strong</strong> and <em>emphasis</em></p>'
            ],
            [
                '<ul><li>foo</li><li>bar</li><li>baz <ol><li>qux</li></ol></li></ul>' .
                '<blockquote><p>Foobar</p></blockquote><br />'
            ],
            [ '<h4>Heading with a <strong>strong</strong></h4>'.
              '<p><span>Always escape your element &acd;</span></p>'.
              '<br />'.
              '<ul><li>Some <strong>element</strong></li><li>are more <em>important than other</em></li></ul>'
            ],
            [ '<h4>Heading with <strong>strong and <em>emphasis</em></strong> text </h4>'.
               '<h5>Some <em>emphasis with<strong>too</strong></em></h5>'
            ],
            [ '<p>Some paragraph with some <span> inline text <br /> with a return </span></p>'
            ],
        ];
    }
}
