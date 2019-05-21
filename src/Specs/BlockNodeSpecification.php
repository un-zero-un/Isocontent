<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

interface BlockNodeSpecification extends Specification
{
    public function isSatisfiedBy(BlockNode $blockNode): bool;
}
