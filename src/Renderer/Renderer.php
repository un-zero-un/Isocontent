<?php

declare(strict_types=1);

namespace Isocontent\Renderer;

use Isocontent\AST\NodeList;

interface Renderer
{
    public function render(NodeList $ast);
}
