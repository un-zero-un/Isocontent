<?php

declare(strict_types=1);

namespace Isocontent\Tests\Parser;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Builder;
use Isocontent\AST\NodeList;
use Isocontent\Exception\UnsupportedFormatException;
use Isocontent\Parser\CommonMarkParser;
use PHPUnit\Framework\TestCase;

class CommonMarkParserTest extends TestCase
{
    public function testItSupportsMarkdown(): void
    {
        $this->assertTrue((new CommonMarkParser())->supportsFormat('markdown'));
    }

    public function testItDoesNotSupportOtherFormats(): void
    {
        $parser = new CommonMarkParser();
        $this->assertFalse($parser->supportsFormat('html'));
        $this->assertFalse($parser->supportsFormat('md'));
        $this->assertFalse($parser->supportsFormat('json'));
    }

    public function testItThrowsIfInputIsNotAString(): void
    {
        $this->expectException(UnsupportedFormatException::class);

        $builder = $this->createMock(Builder::class);

        (new CommonMarkParser())->parse($builder, 123);
    }

    public function testItParsesEmptyInput(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->expects($this->never())->method('addTextNode');
        $builder->expects($this->never())->method('addBlockNode');

        (new CommonMarkParser())->parse($builder, '');
    }

    public function testItParsesParagraphWithText(): void
    {
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addTextNode')->with('Hello world')->willReturn($pBuilder);

        (new CommonMarkParser())->parse($builder, 'Hello world');
    }

    public function testItParsesBoldText(): void
    {
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $strongBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('strong', [])->willReturn($strongBuilder);
        $strongBuilder->expects($this->once())->method('addTextNode')->with('bold')->willReturn($strongBuilder);

        (new CommonMarkParser())->parse($builder, '**bold**');
    }

    public function testItParsesItalicText(): void
    {
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $emBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('emphasis', [])->willReturn($emBuilder);
        $emBuilder->expects($this->once())->method('addTextNode')->with('italic')->willReturn($emBuilder);

        (new CommonMarkParser())->parse($builder, '*italic*');
    }

    public function testItParsesStrikethrough(): void
    {
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $delBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('stripped', [])->willReturn($delBuilder);
        $delBuilder->expects($this->once())->method('addTextNode')->with('deleted')->willReturn($delBuilder);

        (new CommonMarkParser())->parse($builder, '~~deleted~~');
    }

    public function testItParsesHeading(): void
    {
        $builder = $this->createMock(Builder::class);
        $h2Builder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('title', ['level' => 2])->willReturn($h2Builder);
        $h2Builder->expects($this->once())->method('addTextNode')->with('Heading')->willReturn($h2Builder);

        (new CommonMarkParser())->parse($builder, '## Heading');
    }

    public function testItParsesLink(): void
    {
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $aBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('link', ['href' => 'https://example.com'])->willReturn($aBuilder);
        $aBuilder->expects($this->once())->method('addTextNode')->with('click')->willReturn($aBuilder);

        (new CommonMarkParser())->parse($builder, '[click](https://example.com)');
    }

    public function testItParsesBulletList(): void
    {
        $builder = $this->createMock(Builder::class);
        $listBuilder = $this->createMock(Builder::class);
        $itemBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('list', ['ordered' => false])->willReturn($listBuilder);
        $listBuilder->expects($this->once())->method('addBlockNode')->with('list_item', [])->willReturn($itemBuilder);
        $itemBuilder->expects($this->once())->method('addTextNode')->with('item')->willReturn($itemBuilder);

        (new CommonMarkParser())->parse($builder, '- item');
    }

    public function testItParsesOrderedList(): void
    {
        $builder = $this->createMock(Builder::class);
        $listBuilder = $this->createMock(Builder::class);
        $itemBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('list', ['ordered' => true])->willReturn($listBuilder);
        $listBuilder->expects($this->once())->method('addBlockNode')->with('list_item', [])->willReturn($itemBuilder);
        $itemBuilder->expects($this->once())->method('addTextNode')->with('first')->willReturn($itemBuilder);

        (new CommonMarkParser())->parse($builder, '1. first');
    }

    public function testItParsesBlockquote(): void
    {
        $builder = $this->createMock(Builder::class);
        $quoteBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('quote', [])->willReturn($quoteBuilder);
        $quoteBuilder->expects($this->once())->method('addTextNode')->with('wise words')->willReturn($quoteBuilder);

        (new CommonMarkParser())->parse($builder, '> wise words');
    }

    public function testItParsesInlineCode(): void
    {
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $codeBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('code', [])->willReturn($codeBuilder);
        $codeBuilder->expects($this->once())->method('addTextNode')->with('$var')->willReturn($codeBuilder);

        (new CommonMarkParser())->parse($builder, '`$var`');
    }

    public function testItParsesFencedCodeBlock(): void
    {
        $builder = $this->createMock(Builder::class);
        $codeBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('code', [])->willReturn($codeBuilder);
        $codeBuilder->expects($this->once())->method('addTextNode')->with("echo 'hello';")->willReturn($codeBuilder);

        (new CommonMarkParser())->parse($builder, "```php\necho 'hello';\n```");
    }

    public function testItParsesIndentedCodeBlock(): void
    {
        $builder = $this->createMock(Builder::class);
        $codeBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('code', [])->willReturn($codeBuilder);
        $codeBuilder->expects($this->once())->method('addTextNode')->with('code here')->willReturn($codeBuilder);

        (new CommonMarkParser())->parse($builder, '    code here');
    }

    public function testItParsesHorizontalRule(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('separator', [])->willReturn($this->createMock(Builder::class));

        (new CommonMarkParser())->parse($builder, '---');
    }

    public function testItParsesHardLineBreak(): void
    {
        $ast = Builder::create();
        (new CommonMarkParser())->parse($ast, "line one\\\nline two");
        $result = $ast->getAST();

        $this->assertInstanceOf(NodeList::class, $result);
        assert($result instanceof NodeList);

        $paragraph = $result->nodes[0];
        $this->assertInstanceOf(BlockNode::class, $paragraph);
        assert($paragraph instanceof BlockNode);
        $this->assertSame('paragraph', $paragraph->getBlockType());

        $children = $paragraph->getChildren();
        $this->assertNotNull($children);
        assert(null !== $children);
        $this->assertCount(3, $children->nodes);
        $this->assertInstanceOf(\Isocontent\AST\TextNode::class, $children->nodes[0]);
        $this->assertInstanceOf(BlockNode::class, $children->nodes[1]);
        assert($children->nodes[1] instanceof BlockNode);
        $this->assertSame('new_line', $children->nodes[1]->getBlockType());
        $this->assertInstanceOf(\Isocontent\AST\TextNode::class, $children->nodes[2]);
    }

    public function testItParsesImage(): void
    {
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $imgBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('link', ['href' => 'https://example.com/img.png'])->willReturn($imgBuilder);
        $imgBuilder->expects($this->once())->method('addTextNode')->with('alt text')->willReturn($imgBuilder);

        (new CommonMarkParser())->parse($builder, '![alt text](https://example.com/img.png)');
    }

    public function testItUnwrapsParagraphInsideListItem(): void
    {
        // Verify that list items don't get an intermediate paragraph wrapper
        $ast = Builder::create();
        (new CommonMarkParser())->parse($ast, '- item');
        $result = $ast->getAST();

        $this->assertInstanceOf(NodeList::class, $result);
        assert($result instanceof NodeList);
        $listNode = $result->nodes[0];
        $this->assertInstanceOf(BlockNode::class, $listNode);
        assert($listNode instanceof BlockNode);
        $this->assertSame('list', $listNode->getBlockType());

        $children = $listNode->getChildren();
        $this->assertNotNull($children);
        assert(null !== $children);
        $itemNode = $children->nodes[0];
        $this->assertInstanceOf(BlockNode::class, $itemNode);
        assert($itemNode instanceof BlockNode);
        $this->assertSame('list_item', $itemNode->getBlockType());

        // First child of list_item should be TextNode, not a paragraph BlockNode
        $itemChildren = $itemNode->getChildren();
        $this->assertNotNull($itemChildren);
        assert(null !== $itemChildren);
        $this->assertInstanceOf(\Isocontent\AST\TextNode::class, $itemChildren->nodes[0]);
    }

    public function testItUnwrapsParagraphInsideBlockquote(): void
    {
        $ast = Builder::create();
        (new CommonMarkParser())->parse($ast, '> quoted');
        $result = $ast->getAST();

        $this->assertInstanceOf(NodeList::class, $result);
        assert($result instanceof NodeList);
        $quoteNode = $result->nodes[0];
        $this->assertInstanceOf(BlockNode::class, $quoteNode);
        assert($quoteNode instanceof BlockNode);
        $this->assertSame('quote', $quoteNode->getBlockType());

        // First child of quote should be TextNode, not a paragraph BlockNode
        $quoteChildren = $quoteNode->getChildren();
        $this->assertNotNull($quoteChildren);
        assert(null !== $quoteChildren);
        $this->assertInstanceOf(\Isocontent\AST\TextNode::class, $quoteChildren->nodes[0]);
    }

    public function testItParsesImageWithEmptyAlt(): void
    {
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $imgBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('link', ['href' => 'https://example.com/img.png'])->willReturn($imgBuilder);
        $imgBuilder->expects($this->never())->method('addTextNode');

        (new CommonMarkParser())->parse($builder, '![](https://example.com/img.png)');
    }

    public function testItParsesImageWithFormattedAlt(): void
    {
        $builder = $this->createMock(Builder::class);
        $pBuilder = $this->createMock(Builder::class);
        $imgBuilder = $this->createMock(Builder::class);
        $builder->expects($this->once())->method('addBlockNode')->with('paragraph', [])->willReturn($pBuilder);
        $pBuilder->expects($this->once())->method('addBlockNode')->with('link', ['href' => 'https://example.com/img.png'])->willReturn($imgBuilder);
        $imgBuilder->expects($this->once())->method('addTextNode')->with('bold text')->willReturn($imgBuilder);

        (new CommonMarkParser())->parse($builder, '![**bold text**](https://example.com/img.png)');
    }

    public function testItSkipsUnknownBlockNodes(): void
    {
        // HTML blocks produce HtmlBlock nodes, which are not handled by parseBlockType
        $ast = Builder::create();
        (new CommonMarkParser())->parse($ast, "<div>\nhello\n</div>");
        $result = $ast->getAST();

        // HtmlBlock falls through to null, so its children (none for raw HTML) are iterated
        $this->assertInstanceOf(NodeList::class, $result);
    }
}
