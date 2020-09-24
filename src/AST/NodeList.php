<?php

declare(strict_types=1);

namespace Isocontent\AST;

class NodeList
{
    /**
     * @var Node[]
     */
    private array $nodes = [];

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
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
     * @param Node[] $nodes
     *
     * @return NodeList
     */
    public static function fromArray(array $nodes): self
    {
        $nodeList = new self;
        $nodeList->nodes = $nodes;

        return $nodeList;
    }
}
