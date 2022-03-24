<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;

class ArrayParser implements Parser
{
    /**
     * @param Builder      $builder
     * @param array<mixed> $input
     */
    public function parse(Builder $builder, $input): void
    {
        foreach ($input as $node) {
            $this->parseNode($builder, $node);
        }
    }

    public function supportsFormat(string $format): bool
    {
        return 'array' === $format;
    }

    /**
     * @param Builder      $builder
     * @param array<mixed> $node
     */
    private function parseNode(Builder $builder, array $node): void
    {
        if (Node::TYPE_TEXT === $node['type']) {
            $builder->addTextNode($node['value']);

            return;
        }

        $childNodes = $node['children'] ?? null;
        $blockType  = $node['block_type'];

        $childBuilder = $builder->addBlockNode($blockType, $node['arguments']);

        if ($childNodes) {
            foreach ($childNodes as $childNode) {
                $this->parseNode($childBuilder, $childNode);
            }
        }
    }
}
