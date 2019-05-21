<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

class BlockArgumentMatch extends BaseSpecification
{
    /**
     * @var string
     */
    private $argumentName;

    /**
     * @var mixed
     */
    private $argumentValue;

    public function __construct(string $argumentName, $argumentValue)
    {
        $this->argumentName  = $argumentName;
        $this->argumentValue = $argumentValue;
    }

    public function isSatisfiedBy($candidate): bool
    {
        return
            $candidate instanceof BlockNode &&
            ($candidate->getArguments()[$this->argumentName] ?? null) === $this->argumentValue;
    }
}
