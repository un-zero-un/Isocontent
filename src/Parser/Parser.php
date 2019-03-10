<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;

interface Parser
{
    public function parse(Builder $builder, $input): void;
}
