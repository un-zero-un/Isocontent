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

    /**
     * @var array
     */
    private $arguments;

    private function __construct(string $blockType, array $arguments, NodeList $children = null)
    {
        $this->blockType = $blockType;
        $this->children = $children;
        $this->arguments = $arguments;
    }

    public function getBlockType(): string
    {
        return $this->blockType;
    }

    public function getChildren(): ?NodeList
    {
        return $this->children;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getType(): string
    {
        return Node::TYPE_BLOCK;
    }

    public function toArray(): array
    {
        $array = array_merge($this->arguments, ['type' => $this->getType(), 'block_type' => $this->blockType]);
        if ($this->children) {
            $array['children'] = $this->children->toArray();
        }

        return $array;
    }

    public static function fromBlockType(string $blockType, array $arguments = [], NodeList $children = null): self
    {
        return new self($blockType, $arguments, $children);
    }
}
