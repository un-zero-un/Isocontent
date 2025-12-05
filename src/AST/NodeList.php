<?php

declare(strict_types=1);

namespace Isocontent\AST;

final class NodeList
{
    /**
     * @param list<Node> $nodes
     */
    private function __construct(
        public readonly array $nodes = [],
    ) {
    }

    /**
     * @return array<array<mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            function (Node $node) {
                return $node->toArray();
            },
            $this->nodes
        );
    }

    /**
     * @param list<Node> $nodes
     */
    public static function fromArray(array $nodes): self
    {
        return new self($nodes);
    }
}
