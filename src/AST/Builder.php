<?php

declare(strict_types=1);

namespace Isocontent\AST;

class Builder
{
    /**
     * @var Builder[]
     */
    private array $nodes;

    private ?string $type;

    /**
     * @var array<string, mixed>|null
     */
    private ?array $data;

    /**
     * @param array<string, mixed>|null $data
     */
    private function __construct(?string $type = null, ?array $data = null)
    {
        $this->nodes = [];
        $this->type = $type;
        $this->data = $data;
    }

    public function addTextNode(string $text): self
    {
        $this->nodes[] = new self(Node::TYPE_TEXT, ['text' => $text]);

        return $this;
    }

    /**
     * @param array<string, ?scalar> $arguments
     */
    public function addBlockNode(string $blockType, array $arguments = []): self
    {
        $builder = new self(
            Node::TYPE_BLOCK,
            array_merge(['arguments' => $arguments], ['block_type' => $blockType])
        );

        $this->nodes[] = $builder;

        return $builder;
    }

    public function getAST(): NodeList|Node
    {
        $getAst = function (Builder $builder): Node {
            $ast = $builder->getAST();
            assert($ast instanceof Node);

            return $ast;
        };

        switch ($this->type) {
            case Node::TYPE_TEXT:
                return TextNode::fromText($this->data['text'] ?? '');

            case Node::TYPE_BLOCK:
                assert(isset($this->data['block_type']));

                return BlockNode::fromBlockType(
                    $this->data['block_type'],
                    $this->data['arguments'],
                    0 === count($this->nodes) ? null : NodeList::fromArray(array_map($getAst, $this->nodes))
                );

            default:
                return NodeList::fromArray(array_map($getAst, $this->nodes));
        }
    }

    public static function create(): self
    {
        return new self();
    }
}
