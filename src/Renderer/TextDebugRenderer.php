<?php

namespace Isocontent\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;

final class TextDebugRenderer implements Renderer
{
    #[\Override]
    public function render(NodeList $ast): string
    {
        return $this->renderNodeList($ast);
    }

    #[\Override]
    public function supportsFormat(string $format): bool
    {
        return 'text_debug' === $format;
    }

    private function renderNodeList(NodeList $ast, ?int $level = 0): string
    {
        return implode(
            '',
            array_map(
                fn (Node $node) => $this->renderNode($node, $level),
                $ast->nodes,
            )
        );
    }

    private function renderNode(Node $node, ?int $level = 0): string
    {
        $level ??= 0;
        $renderedNode = str_repeat('  ', $level).'# '.$node->getType().$this->renderArguments($node);

        if ($node instanceof BlockNode && null !== $node->getChildren()) {
            return
                $renderedNode
                ."\n"
                .$this->renderNodeList($node->getChildren(), $level + 1);
        }

        return $renderedNode."\n";
    }

    private function renderArguments(Node $node): string
    {
        if ($node instanceof TextNode) {
            return sprintf('(%s)', $node->getValue());
        }

        if ($node instanceof BlockNode) {
            return sprintf(
                '(%s)',
                implode(
                    ', ',
                    array_map(
                        static fn (int|float|string|bool $value, string $key) => $key.'='.((string) $value),
                        array_values($node->getArguments() + [$node->getBlockType()]),
                        array_keys($node->getArguments()) + ['type']
                    )
                )
            );
        }

        return '';
    }
}
