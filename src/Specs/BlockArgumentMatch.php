<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

class BlockArgumentMatch extends BaseSpecification
{
    private string $argumentName;

    /**
     * @var mixed
     */
    private $argumentValue;

    /**
     * @param string $argumentName
     * @param mixed  $argumentValue
     */
    public function __construct(string $argumentName, $argumentValue)
    {
        $this->argumentName  = $argumentName;
        $this->argumentValue = $argumentValue;
    }

    /**
     * @param mixed $candidate
     *
     * @return bool
     */
    public function isSatisfiedBy($candidate): bool
    {
        return
            $candidate instanceof BlockNode &&
            ($candidate->getArguments()[$this->argumentName] ?? null) === $this->argumentValue;
    }
}
