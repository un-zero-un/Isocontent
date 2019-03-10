<?php

declare(strict_types=1);

namespace Isocontent\Renderer;

use Isocontent\AST\NodeList;

class JSONRenderer implements Renderer
{
    public function render(NodeList $ast)
    {
        return json_encode($ast->toArray());
    }

    public function supportsFormat(string $format): bool
    {
        return 'json' === $format;
    }
}
