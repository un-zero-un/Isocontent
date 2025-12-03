<?php

declare(strict_types=1);

namespace Isocontent\Renderer;

use Isocontent\AST\NodeList;

final class JSONRenderer implements Renderer
{
    #[\Override]
    public function render(NodeList $ast): string
    {
        return json_encode($ast->toArray(), JSON_THROW_ON_ERROR);
    }

    #[\Override]
    public function supportsFormat(string $format): bool
    {
        return 'json' === $format;
    }
}
