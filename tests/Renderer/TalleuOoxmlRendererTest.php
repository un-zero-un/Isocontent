<?php

declare(strict_types=1);

namespace Isocontent\Tests\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Exception\UnknownNodeTypeException;
use Isocontent\Renderer\TalleuOoxmlRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TalleuOoxmlRendererTest extends TestCase
{
    public function testItSupportsOoxml(): void
    {
        $this->assertTrue((new TalleuOoxmlRenderer())->supportsFormat('ooxml'));
    }

    public function testItDoesNotSupportOtherFormats(): void
    {
        $renderer = new TalleuOoxmlRenderer();
        $this->assertFalse($renderer->supportsFormat('html'));
        $this->assertFalse($renderer->supportsFormat('json'));
        $this->assertFalse($renderer->supportsFormat('docx'));
    }

    public function testItRendersEmptyAst(): void
    {
        $result = (new TalleuOoxmlRenderer())->render(NodeList::fromArray([]));

        $this->assertStringStartsWith('<?xml version="1.0"', $result);
        $this->assertStringContainsString('<w:body>', $result);
        $this->assertStringContainsString('</w:body>', $result);
        $this->assertStringContainsString('</w:document>', $result);
    }

    public function testItRendersParagraphWithText(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                TextNode::fromText('Hello world'),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('<w:p>', $result);
        $this->assertStringContainsString('<w:t xml:space="preserve">Hello world</w:t>', $result);
    }

    #[DataProvider('inlineFormattingProvider')]
    public function testItRendersInlineFormatting(string $blockType, string $expectedXml): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                BlockNode::fromBlockType($blockType, [], NodeList::fromArray([
                    TextNode::fromText('text'),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString($expectedXml, $result);
        $this->assertStringContainsString('text', $result);
    }

    public static function inlineFormattingProvider(): array
    {
        return [
            'bold' => ['strong', '<w:b/>'],
            'italic' => ['emphasis', '<w:i/>'],
            'strikethrough' => ['stripped', '<w:strike/>'],
        ];
    }

    public function testItRendersNestedFormatting(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                BlockNode::fromBlockType('strong', [], NodeList::fromArray([
                    BlockNode::fromBlockType('emphasis', [], NodeList::fromArray([
                        TextNode::fromText('bold and italic'),
                    ])),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('<w:b/>', $result);
        $this->assertStringContainsString('<w:i/>', $result);
        $this->assertStringContainsString('bold and italic', $result);
    }

    public function testItRendersTitle(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('title', ['level' => 2], NodeList::fromArray([
                TextNode::fromText('My Heading'),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('Heading2', $result);
        $this->assertStringContainsString('My Heading', $result);
    }

    public function testItRendersLink(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                BlockNode::fromBlockType('link', ['href' => 'https://example.com'], NodeList::fromArray([
                    TextNode::fromText('click here'),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('HYPERLINK', $result);
        $this->assertStringContainsString('https://example.com', $result);
        $this->assertStringContainsString('click here', $result);
    }

    public function testItRendersBulletList(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('list', ['ordered' => false], NodeList::fromArray([
                BlockNode::fromBlockType('list_item', [], NodeList::fromArray([
                    TextNode::fromText('Item 1'),
                ])),
                BlockNode::fromBlockType('list_item', [], NodeList::fromArray([
                    TextNode::fromText('Item 2'),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('<w:numId w:val="1"/>', $result);
        $this->assertStringContainsString('Item 1', $result);
        $this->assertStringContainsString('Item 2', $result);
    }

    public function testItRendersOrderedList(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('list', ['ordered' => true], NodeList::fromArray([
                BlockNode::fromBlockType('list_item', [], NodeList::fromArray([
                    TextNode::fromText('First'),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('<w:numId w:val="2"/>', $result);
        $this->assertStringContainsString('First', $result);
    }

    public function testItRendersBlockquote(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('quote', [], NodeList::fromArray([
                TextNode::fromText('A wise quote'),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('<w:ind w:left="720"/>', $result);
        $this->assertStringContainsString('A wise quote', $result);
    }

    public function testItRendersInlineCode(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                BlockNode::fromBlockType('code', [], NodeList::fromArray([
                    TextNode::fromText('$var'),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('Courier New', $result);
        $this->assertStringContainsString('$var', $result);
    }

    #[DataProvider('leafBlockProvider')]
    public function testItRendersLeafBlock(string $blockType, string $expectedXml): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType($blockType),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString($expectedXml, $result);
    }

    public static function leafBlockProvider(): array
    {
        return [
            'new_line' => ['new_line', '<w:p/>'],
            'separator' => ['separator', '<w:pBdr>'],
        ];
    }

    public function testItRendersInlineTextAsTransparentContainer(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                BlockNode::fromBlockType('inline_text', [], NodeList::fromArray([
                    TextNode::fromText('inline'),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('inline', $result);
        $this->assertStringContainsString('<w:p>', $result);
    }

    public function testItRendersComplexDocument(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('title', ['level' => 1], NodeList::fromArray([
                TextNode::fromText('Document Title'),
            ])),
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                TextNode::fromText('A paragraph with '),
                BlockNode::fromBlockType('strong', [], NodeList::fromArray([
                    TextNode::fromText('bold'),
                ])),
                TextNode::fromText(' text.'),
            ])),
            BlockNode::fromBlockType('list', ['ordered' => false], NodeList::fromArray([
                BlockNode::fromBlockType('list_item', [], NodeList::fromArray([
                    TextNode::fromText('Item one'),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringStartsWith('<?xml version="1.0"', $result);
        $this->assertStringContainsString('Heading1', $result);
        $this->assertStringContainsString('Document Title', $result);
        $this->assertStringContainsString('A paragraph with ', $result);
        $this->assertStringContainsString('<w:b/>', $result);
        $this->assertStringContainsString('bold', $result);
        $this->assertStringContainsString(' text.', $result);
        $this->assertStringContainsString('Item one', $result);
        $this->assertStringContainsString('<w:numId', $result);
    }

    public function testItRendersLinkWithoutHref(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                BlockNode::fromBlockType('link', [], NodeList::fromArray([
                    TextNode::fromText('orphan'),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('orphan', $result);
        $this->assertStringContainsString('HYPERLINK', $result);
    }

    public function testItEscapesSpecialXmlCharacters(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                TextNode::fromText('a < b & c > d'),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('a &lt; b &amp; c &gt; d', $result);
    }

    public function testItExtractsPlainTextFromNestedLinkChildren(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                BlockNode::fromBlockType('link', ['href' => 'https://example.com'], NodeList::fromArray([
                    BlockNode::fromBlockType('strong', [], NodeList::fromArray([
                        TextNode::fromText('bold link'),
                    ])),
                ])),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('bold link', $result);
        $this->assertStringContainsString('https://example.com', $result);
    }

    public function testItHandlesBlockNodeWithNullChildren(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph'),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('<w:p></w:p>', $result);
    }

    public function testItHandlesCodeWithNullChildren(): void
    {
        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                BlockNode::fromBlockType('code'),
            ])),
        ]);

        $result = (new TalleuOoxmlRenderer())->render($ast);

        $this->assertStringContainsString('Courier New', $result);
    }

    public function testItThrowsWithUnknownImplementationOfNode(): void
    {
        $this->expectException(UnknownNodeTypeException::class);
        $this->expectExceptionMessage('Unsupported node type:');

        $ast = NodeList::fromArray([
            BlockNode::fromBlockType('paragraph', [], NodeList::fromArray([
                new class implements Node {
                    public function getType(): string
                    {
                        return 'unknown';
                    }

                    public function toArray(): array
                    {
                        return [];
                    }
                },
            ])),
        ]);

        (new TalleuOoxmlRenderer())->render($ast);
    }
}
