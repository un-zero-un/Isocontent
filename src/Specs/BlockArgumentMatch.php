<?php

namespace Isocontent\Specs;

use Isocontent\AST\BlockNode;

final class BlockArgumentMatch extends BaseSpecification
{
    public function __construct(
        private readonly string $argumentName,
        private readonly int|float|bool|string $argumentValue,
    ) {
    }

    #[\Override]
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return
            $candidate instanceof BlockNode
            && ($candidate->getArguments()[$this->argumentName] ?? null) === $this->argumentValue;
    }
}
