<?php

declare(strict_types=1);

namespace Isocontent\Bridge\Twig\Extension;

use Isocontent\AST\NodeList;
use Isocontent\Isocontent;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class IsocontentExtension extends AbstractExtension
{
    private Isocontent $isocontent;

    public function __construct(Isocontent $isocontent)
    {
        $this->isocontent = $isocontent;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('render_isocontent_ast', [$this, 'renderAST'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<array<mixed>>|NodeList $ast
     */
    public function renderAST($ast, string $format = 'html'): string
    {
        if (!$ast instanceof NodeList) {
            /** @var NodeList $ast */
            $ast = $this->isocontent->buildAST($ast, 'array');
        }

        return $this->isocontent->render($ast, $format);
    }
}
