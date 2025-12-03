<?php

declare(strict_types=1);

namespace Isocontent\AST;

final class Builder
{
    /**
     * @var list<Builder>
     */
    private array $nodes;

    /**
     * @param array{
     *     text?: string,
     *     block_type?: string,
     *     arguments?: array<string, scalar>
     * }|null $data
     */
    private function __construct(
        private readonly ?string $type = null,
        private readonly ?array $data = null,
    ) {
        $this->nodes = [];
    }

    public function addTextNode(string $text): self
    {
        $this->nodes[] = new self(Node::TYPE_TEXT, ['text' => $text]);

        return $this;
    }

    /**
     * @param array<string, scalar> $arguments
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
                assert(isset($this->data['text']));

                return TextNode::fromText($this->data['text']);

            case Node::TYPE_BLOCK:
                assert(isset($this->data['block_type']));
                assert(isset($this->data['arguments']));

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
