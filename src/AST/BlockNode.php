<?php

declare(strict_types=1);

namespace Isocontent\AST;

final class BlockNode implements Node
{
    private string $blockType;

    private ?NodeList $children;

    /**
     * @var array<string, scalar>
     */
    private array $arguments;

    /**
     * @param string                $blockType
     * @param array<string, scalar> $arguments
     * @param NodeList|null         $children
     */
    private function __construct(string $blockType, array $arguments, NodeList $children = null)
    {
        $this->blockType = $blockType;
        $this->children  = $children;
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

    /**
     * @return array<string, scalar>
     */
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
        $array = ['type' => $this->getType(), 'block_type' => $this->blockType, 'arguments' => $this->arguments];
        if ($this->children) {
            $array['children'] = $this->children->toArray();
        }

        return $array;
    }

    /**
     * @param string                $blockType
     * @param array<string, scalar> $arguments
     * @param NodeList|null         $children
     *
     * @return self
     */
    public static function fromBlockType(string $blockType, array $arguments = [], NodeList $children = null): self
    {
        return new self($blockType, $arguments, $children);
    }
}
