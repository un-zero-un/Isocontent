<?php

namespace Isocontent\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;

class TextDebugRenderer implements Renderer
{
    public function render(NodeList $ast): string
    {
        return $this->renderNodeList($ast);
    }

    public function supportsFormat(string $format): bool
    {
        return 'text_debug' === $format;
    }

    private function renderNodeList(NodeList $ast, ?int $level = 0): string
    {
        return implode(
            '',
            array_map(
                function (Node $node) use ($level) { return $this->renderNode($node, $level); },
                $ast->getNodes()
            )
        );
    }

    private function renderNode(Node $node, ?int $level = 0): string
    {
        $renderedNode = str_repeat('  ', $level) . '# ' . $node->getType() . $this->renderArguments($node);

        if ($node instanceof BlockNode && null !== $node->getChildren()) {
            return
                $renderedNode .
                "\n" .
                $this->renderNodeList($node->getChildren(), $level + 1);
        }

        return $renderedNode . "\n";
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
                        static function (string $value, string $key) { return $key . '=' . $value; },
                        array_values($node->getArguments() + [$node->getBlockType()]),
                        array_keys($node->getArguments()) + ['type']
                    )
                )
            );
        }

        return '';
    }
}
