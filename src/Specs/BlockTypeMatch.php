<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

class BlockTypeMatch extends BaseSpecification
{
    /**
     * @var string
     */
    private $blockType;

    public function __construct(string $blockType)
    {
        $this->blockType = $blockType;
    }

    /**
     * @param mixed $candidate
     *
     * @return bool
     */
    public function isSatisfiedBy($candidate): bool
    {
        return $candidate instanceof BlockNode && $candidate->getBlockType() === $this->blockType;
    }
}
