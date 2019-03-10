<?php

declare(strict_types=1);

namespace Isocontent\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;

class HTMLRenderer
{
    private const TAGS = [
        'paragraph' => 'p',
        'inline_text' => 'span',
        'emphasis' => 'em',
        'strong' => 'strong',
        'generic' => 'span',
    ];

    public function render(NodeList $ast)
    {
        return array_reduce(
            $ast->getNodes(),
            function (string $memo, Node $node) {
                if ($node instanceof TextNode) {
                    return $memo . $node->getValue();
                }

                if ($node instanceof BlockNode) {
                    return $memo . $this->renderBlockNode($node);
                }

                return $memo;
            },
            ''
        );
    }

    private function renderBlockNode(BlockNode $blockNode): string
    {
        if (null === $blockNode->getChildren()) {
            return strtr(
                '<:tagName: />',
                [
                    ':tagName:' => self::TAGS[$blockNode->getBlockType()] ?? 'span',
                ]
            );
        }

        return strtr(
            '<:tagName:>:content:</:tagName:>',
            [
                ':tagName:' => self::TAGS[$blockNode->getBlockType()] ?? 'span',
                ':content:' => $this->render($blockNode->getChildren()),
            ]
        );
    }
}
