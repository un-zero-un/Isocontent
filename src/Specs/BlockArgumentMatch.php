<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

class BlockArgumentMatch extends BaseSpecification
{
    private string $argumentName;

    private $argumentValue;

    public function __construct(string $argumentName, $argumentValue)
    {
        $this->argumentName = $argumentName;
        $this->argumentValue = $argumentValue;
    }

    #[\Override]
    public function isSatisfiedBy($candidate): bool
    {
        return
            $candidate instanceof BlockNode
            && ($candidate->getArguments()[$this->argumentName] ?? null) === $this->argumentValue;
    }
}
