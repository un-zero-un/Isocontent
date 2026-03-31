<?php

declare(strict_types=1);

namespace Isocontent\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Exception\FeatureNotAvailableException;
use Isocontent\Exception\UnknownNodeTypeException;
use Talleu\MdToOoxml\Node\BlankLineNode;
use Talleu\MdToOoxml\Node\DocumentNode;
use Talleu\MdToOoxml\Node\HorizontalRuleNode;
use Talleu\MdToOoxml\Node\InlineCodeNode;
use Talleu\MdToOoxml\Node\LinkNode;
use Talleu\MdToOoxml\Node\ListItemNode;
use Talleu\MdToOoxml\Node\NodeInterface;
use Talleu\MdToOoxml\Node\ParagraphNode;
use Talleu\MdToOoxml\Node\QuoteNode;
use Talleu\MdToOoxml\Node\TextRunNode;
use Talleu\MdToOoxml\Node\TitleNode;
use Talleu\MdToOoxml\OoXmlConverterFactory;
use Talleu\MdToOoxml\Renderer\NodeRenderer;

final class TalleuOoxmlRenderer implements Renderer
{
    private NodeRenderer $nodeRenderer;

    public function __construct(?NodeRenderer $nodeRenderer = null)
    {
        $this->nodeRenderer = $nodeRenderer ?? OoXmlConverterFactory::createRenderer();
    }

    #[\Override]
    public function render(NodeList $ast): string
    {
        if (!class_exists(OoXmlConverterFactory::class)) {
            throw new FeatureNotAvailableException(OoXmlConverterFactory::class, __CLASS__); // @codeCoverageIgnore
        }

        $document = new DocumentNode();

        foreach ($ast->nodes as $node) {
            $this->mapNode($node, $document, []);
        }

        return $this->nodeRenderer->render($document);
    }

    #[\Override]
    public function supportsFormat(string $format): bool
    {
        return 'ooxml' === $format && class_exists(OoXmlConverterFactory::class);
    }

    /**
     * @param array{is_bold?: bool, is_italic?: bool, is_strikethrough?: bool, list_type?: string} $context
     */
    private function mapNode(Node $node, NodeInterface $parent, array $context): void
    {
        if ($node instanceof TextNode) {
            $parent->addChild(
                new TextRunNode(
                    $node->getValue(),
                    isBold: $context['is_bold'] ?? false,
                    isItalic: $context['is_italic'] ?? false,
                    isStrikethrough: $context['is_strikethrough'] ?? false,
                ),
            );

            return;
        }

        if (!$node instanceof BlockNode) {
            throw new UnknownNodeTypeException('Unsupported node type: ' . $node::class);
        }

        switch ($node->getBlockType()) {
            case 'paragraph':
                $this->mapBlock($node, $parent, $context, new ParagraphNode());
                break;
            case 'title':
                $this->mapBlock($node, $parent, $context, new TitleNode((int) ($node->getArguments()['level'] ?? 1)));
                break;
            case 'quote':
                $this->mapBlock($node, $parent, $context, new QuoteNode());
                break;
            case 'list_item':
                $this->mapBlock($node, $parent, $context, new ListItemNode($context['list_type'] ?? 'bullet'));
                break;
            case 'list':
                $context['list_type'] = ($node->getArguments()['ordered'] ?? false) ? 'number' : 'bullet';
                $this->mapInline($node, $parent, $context);
                break;
            case 'strong':
                $context['is_bold'] = true;
                $this->mapInline($node, $parent, $context);
                break;
            case 'emphasis':
                $context['is_italic'] = true;
                $this->mapInline($node, $parent, $context);
                break;
            case 'stripped':
                $context['is_strikethrough'] = true;
                $this->mapInline($node, $parent, $context);
                break;
            case 'link':
                $parent->addChild(new LinkNode($this->extractPlainText($node), (string) ($node->getArguments()['href'] ?? '')));
                break;
            case 'code':
                $parent->addChild(new InlineCodeNode($this->extractPlainText($node)));
                break;
            case 'new_line':
                $parent->addChild(new BlankLineNode());
                break;
            case 'separator':
                $parent->addChild(new HorizontalRuleNode());
                break;
            default:
                $this->mapInline($node, $parent, $context);
                break;
        }
    }

    /**
     * @param array{is_bold?: bool, is_italic?: bool, is_strikethrough?: bool, list_type?: string} $context
     */
    private function mapBlock(BlockNode $node, NodeInterface $parent, array $context, NodeInterface $block): void
    {
        $parent->addChild($block);
        $this->mapInline($node, $block, $context);
    }

    /**
     * @param array{is_bold?: bool, is_italic?: bool, is_strikethrough?: bool, list_type?: string} $context
     */
    private function mapInline(BlockNode $node, NodeInterface $parent, array $context): void
    {
        if (null === $node->getChildren()) {
            return;
        }

        foreach ($node->getChildren()->nodes as $child) {
            $this->mapNode($child, $parent, $context);
        }
    }

    private function extractPlainText(BlockNode $node): string
    {
        if (null === $node->getChildren()) {
            return '';
        }

        $text = '';

        foreach ($node->getChildren()->nodes as $child) {
            if ($child instanceof TextNode) {
                $text .= $child->getValue();
            } elseif ($child instanceof BlockNode) {
                $text .= $this->extractPlainText($child);
            }
        }

        return $text;
    }
}
