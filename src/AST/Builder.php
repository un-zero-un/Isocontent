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
     * @var array|null
     */
    private $data;

    private function __construct(string $type = null, array $data = null)
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

    public function addBlockNode(string $blockType): self
    {
        $builder = new self(Node::TYPE_BLOCK, ['block_type' => $blockType]);
        $this->nodes[] = $builder;

        return $builder;
    }

    public function getAST()
    {
        $getAst = function (Builder $builder) {
            return $builder->getAST();
        };

        switch ($this->type) {
            case null:
                return NodeList::fromArray(array_map($getAst, $this->nodes));

            case Node::TYPE_TEXT:
                return TextNode::fromText($this->data['text']);

            case Node::TYPE_BLOCK:
                return BlockNode::fromBlockType(
                    $this->data['block_type'],
                    0 === count($this->nodes) ? null : NodeList::fromArray(array_map($getAst, $this->nodes))
                );

            default:
                throw new UnknownNodeTypeException(sprintf('Unknown node type "%s".', $this->type));
        }
    }

    public static function create(): self
    {
        return new self;
    }
}
