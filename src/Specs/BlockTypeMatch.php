<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

class BlockTypeMatch extends BaseSpecification
{
    private string $blockType;

    public function __construct(string $blockType)
    {
        $this->blockType = $blockType;
    }

    #[\Override]
    public function isSatisfiedBy($candidate): bool
    {
        return $candidate instanceof BlockNode && $candidate->getBlockType() === $this->blockType;
    }
}
