<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

class BlockTypeMatch implements BlockNodeSpecification
{
    use SpecificationImpl;

    /**
     * @var string
     */
    private $blockType;

    public function __construct(string $blockType)
    {
        $this->blockType = $blockType;
    }

    public function isSatisfiedBy(BlockNode $blockNode): bool
    {
        return $blockNode->getBlockType() === $this->blockType;
    }
}
