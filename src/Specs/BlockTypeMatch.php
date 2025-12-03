<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

final class BlockTypeMatch extends BaseSpecification
{
    public function __construct(private readonly string $blockType)
    {
    }

    #[\Override]
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate instanceof BlockNode && $candidate->getBlockType() === $this->blockType;
    }
}
