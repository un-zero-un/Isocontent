<?php

declare(strict_types=1);

namespace Isocontent\Renderer;

use Isocontent\AST\NodeList;

interface Renderer
{
    /**
     * @param NodeList $ast
     *
     * @return mixed
     */
    public function render(NodeList $ast);

    public function supportsFormat(string $format): bool;
}
