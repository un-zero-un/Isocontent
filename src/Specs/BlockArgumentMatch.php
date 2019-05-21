<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

class BlockArgumentMatch implements BlockNodeSpecification
{
    use SpecificationImpl;

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

    public function isSatisfiedBy(BlockNode $blockNode): bool
    {
        return ($blockNode->getArguments()[$this->argumentName] ?? null) === $this->argumentValue;
    }
}
