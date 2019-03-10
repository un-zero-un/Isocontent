<?php

declare(strict_types=1);

namespace Isocontent\AST;

interface Node
{
    public const TYPE_TEXT = 'text';
    public const TYPE_BLOCK = 'block';

    public function getType(): string;

    public function toArray(): array;
}
