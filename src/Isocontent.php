<?php

declare(strict_types=1);

namespace Isocontent;

use Isocontent\AST\Builder;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\Exception\UnsupportedFormatException;
use Isocontent\Parser\Parser;
use Isocontent\Renderer\Renderer;

/**
 * @api
 */
class Isocontent
{
    /**
     * @param iterable<Parser>   $parsers
     * @param iterable<Renderer> $renderers
     */
    public function __construct(
        private readonly iterable $parsers,
        private readonly iterable $renderers,
    ) {
    }

    public function buildAST(mixed $input, string $format): Node|NodeList
    {
        $builder = Builder::create();
        foreach ($this->parsers as $parser) {
            if (!$parser->supportsFormat($format)) {
                continue;
            }

            $parser->parse($builder, $input);

            return $builder->getAST();
        }

        throw new UnsupportedFormatException(sprintf('No parser found for format "%s"', $format));
    }

    public function render(NodeList $ast, string $format): mixed
    {
        foreach ($this->renderers as $renderer) {
            if (!$renderer->supportsFormat($format)) {
                continue;
            }

            return $renderer->render($ast);
        }

        throw new UnsupportedFormatException(sprintf('No renderer found for format "%s"', $format));
    }

    /**
     * @return list<Parser>
     */
    public function getParsers(): array
    {
        return iterator_to_array($this->parsers);
    }

    /**
     * @return list<Renderer>
     */
    public function getRenderers(): array
    {
        return iterator_to_array($this->renderers);
    }
}
