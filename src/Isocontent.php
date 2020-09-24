<?php

declare(strict_types=1);

namespace Isocontent;

use Isocontent\AST\Builder;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\Exception\UnsupportedFormatException;
use Isocontent\Parser\Parser;
use Isocontent\Renderer\Renderer;

class Isocontent
{
    /**
     * @var Parser[]
     */
    private array $parsers;

    /**
     * @var Renderer[]
     */
    private array $renderers;

    /**
     * @param iterable<Parser>   $parsers
     * @param iterable<Renderer> $renderers
     */
    public function __construct(iterable $parsers, iterable $renderers)
    {
        if ($parsers instanceof \Traversable) {
            $this->parsers = iterator_to_array($parsers);
        } else {
            $this->parsers = $parsers;
        }

        if ($renderers instanceof \Traversable) {
            $this->renderers = iterator_to_array($renderers);
        } else {
            $this->renderers = $renderers;
        }
    }

    /**
     * @param mixed  $input
     * @param string $format
     *
     * @return NodeList|Node
     */
    public function buildAST($input, string $format)
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

    /**
     * @param NodeList $ast
     * @param string   $format
     *
     * @return mixed
     */
    public function render(NodeList $ast, string $format)
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
     * @return Parser[]
     */
    public function getParsers(): array
    {
        return $this->parsers;
    }

    /**
     * @return Renderer[]
     */
    public function getRenderers(): array
    {
        return $this->renderers;
    }
}
