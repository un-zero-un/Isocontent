<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;

interface Parser
{
    /**
     * @param mixed $input
     */
    public function parse(Builder $builder, $input): void;

    public function supportsFormat(string $format): bool;
}
