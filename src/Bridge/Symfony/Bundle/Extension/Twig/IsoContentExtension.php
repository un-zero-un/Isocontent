<?php

namespace Isocontent\Bridge\Symfony\Bundle\Extension\Twig;

use Isocontent\AST\NodeList;
use Isocontent\Renderer\HTMLRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for displaying IsoContent
 *
 * @author Yohan Giarelli <yohan@un-zero-un.fr>
 */
class IsoContentExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('display_isocontent', [$this, 'displayIsoContent']),
        ];
    }

    public function displayIsoContent(NodeList $ast): ?string
    {
        return (new HTMLRenderer)->render($ast);
    }
}
