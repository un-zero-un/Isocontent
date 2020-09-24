<?php

declare(strict_types=1);

namespace Isocontent\AST;

use Isocontent\Exception\UnknownNodeTypeException;

class Builder
{
    /**
     * @var Builder[]
     */
    private $nodes;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var array<string, mixed>|null
     */
    private $data;

    /**
     * Builder constructor.
     *
     * @param string|null               $type
     * @param array<string, mixed>|null $data
     */
    private function __construct(string $type = null, array $data = null)
    {
        $this->nodes = [];
        $this->type  = $type;
        $this->data  = $data;
    }

    public function addTextNode(string $text): self
    {
        $this->nodes[] = new self(Node::TYPE_TEXT, ['text' => $text]);

        return $this;
    }

    /**
     * @param string                $blockType
     * @param array<string, scalar> $arguments
     *
     * @return self
     */
    public function addBlockNode(string $blockType, array $arguments = []): self
    {
        $builder       = new self(
            Node::TYPE_BLOCK,
            array_merge(['arguments' => $arguments], ['block_type' => $blockType])
        );
        $this->nodes[] = $builder;

        return $builder;
    }

    /**
     * @return Node|NodeList
     */
    public function getAST()
    {
        $getAst = function (Builder $builder) {
            return $builder->getAST();
        };

        switch ($this->type) {
            case Node::TYPE_TEXT:
                return TextNode::fromText($this->data['text']);

            case Node::TYPE_BLOCK:
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
        return new self;
    }
}
