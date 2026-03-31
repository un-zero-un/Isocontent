<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;
use Isocontent\Exception\FeatureNotAvailableException;
use Isocontent\Exception\UnsupportedFormatException;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\Strikethrough\Strikethrough;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;

final class CommonMarkParser implements Parser
{
    #[\Override]
    public function supportsFormat(string $format): bool
    {
        return 'markdown' === $format && class_exists(MarkdownParser::class);
    }

    #[\Override]
    public function parse(Builder $builder, mixed $input): void
    {
        if (!class_exists(MarkdownParser::class)) {
            throw new FeatureNotAvailableException(MarkdownParser::class, __CLASS__); // @codeCoverageIgnore
        }

        if (!is_string($input)) {
            throw new UnsupportedFormatException();
        }

        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());

        if (class_exists(StrikethroughExtension::class)) {
            $environment->addExtension(new StrikethroughExtension()); // @codeCoverageIgnore
        }

        $parser = new MarkdownParser($environment);
        $document = $parser->parse($input);

        foreach ($document->children() as $child) {
            $this->parseNode($builder, $child);
        }
    }

    private function parseNode(Builder $builder, Node $node, bool $unwrapParagraph = false): void
    {
        // Text content
        if ($node instanceof Text) {
            $builder->addTextNode($node->getLiteral());

            return;
        }

        // Inline code
        if ($node instanceof Code) {
            $childBuilder = $builder->addBlockNode('code');
            $childBuilder->addTextNode($node->getLiteral());

            return;
        }

        // Soft/hard line break
        if ($node instanceof Newline) {
            $builder->addBlockNode('new_line');

            return;
        }

        // Fenced / indented code blocks
        if ($node instanceof FencedCode || $node instanceof IndentedCode) {
            $childBuilder = $builder->addBlockNode('code');
            $childBuilder->addTextNode(rtrim($node->getLiteral()));

            return;
        }

        // Horizontal rule
        if ($node instanceof ThematicBreak) {
            $builder->addBlockNode('separator');

            return;
        }

        // Paragraph unwrapping inside list items / blockquotes
        if ($node instanceof Paragraph && $unwrapParagraph) {
            foreach ($node->children() as $child) {
                $this->parseNode($builder, $child);
            }

            return;
        }

        // Image (no dedicated block type — map to link with alt text)
        if ($node instanceof Image) {
            $childBuilder = $builder->addBlockNode('link', array_filter(['href' => $node->getUrl()]));
            $alt = $this->extractPlainText($node);

            if ('' !== $alt) {
                $childBuilder->addTextNode($alt);
            }

            return;
        }

        // Container blocks / inlines
        $blockType = $this->parseBlockType($node);

        if (null === $blockType) {
            return;
        }

        $childBuilder = $builder->addBlockNode($blockType[0], $blockType[1] ?? []);
        $childUnwrap = $node instanceof ListItem || $node instanceof BlockQuote;

        foreach ($node->children() as $child) {
            $this->parseNode($childBuilder, $child, $childUnwrap);
        }
    }

    /**
     * @return array{0: string, 1?: array<string, scalar>}|null
     */
    private function parseBlockType(Node $node): ?array
    {
        if ($node instanceof Paragraph) {
            return ['paragraph'];
        }

        if ($node instanceof Heading) {
            return ['title', ['level' => $node->getLevel()]];
        }

        if ($node instanceof Strong) {
            return ['strong'];
        }

        if ($node instanceof Emphasis) {
            return ['emphasis'];
        }

        if ($node instanceof Strikethrough) {
            return ['stripped'];
        }

        if ($node instanceof Link) {
            return ['link', array_filter(['href' => $node->getUrl()])];
        }

        if ($node instanceof BlockQuote) {
            return ['quote'];
        }

        if ($node instanceof ListBlock) {
            return ['list', ['ordered' => ListBlock::TYPE_ORDERED === $node->getListData()->type]];
        }

        if ($node instanceof ListItem) {
            return ['list_item'];
        }

        return null;
    }

    private function extractPlainText(Node $node): string
    {
        $text = '';

        foreach ($node->children() as $child) {
            if ($child instanceof Text) {
                $text .= $child->getLiteral();
            } else {
                $text .= $this->extractPlainText($child);
            }
        }

        return $text;
    }
}
