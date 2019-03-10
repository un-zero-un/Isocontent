<?php

declare(strict_types=1);

namespace Isocontent\AST;

final class BlockNode implements Node
{
    /**
     * @var string
     */
    private $blockType;

    /**
     * @var NodeList|null
     */
    private $children;

    private function __construct(string $blockType, NodeList $children = null)
    {
        $this->blockType = $blockType;
        $this->children = $children;
    }

    public function getBlockType(): string
    {
        return $this->blockType;
    }

    public function getChildren(): ?NodeList
    {
        return $this->children;
    }

    public function getType(): string
    {
        return Node::TYPE_BLOCK;
    }

    public function toArray(): array
    {
        $array = ['type' => $this->getType(), 'block_type' => $this->blockType];
        if ($this->children) {
            $array['children'] = $this->children->toArray();
        }

        return $array;
    }

    public static function fromBlockType(string $blockType, NodeList $children = null): self
    {
        return new self($blockType, $children);
    }
}
