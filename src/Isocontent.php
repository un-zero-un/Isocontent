<?php

declare(strict_types=1);

namespace Isocontent;

use Isocontent\AST\Builder;
use Isocontent\AST\NodeList;
use Isocontent\Exception\UnsupportedFormatException;
use Isocontent\Parser\Parser;

class Isocontent
{
    /**
     * @var Parser[]
     */
    private $parsers;

    public function __construct(iterable $parsers)
    {
        if ($parsers instanceof \Traversable) {
            $this->parsers = iterator_to_array($parsers);
        } else {
            $this->parsers = $parsers;
        }
    }

    public function buildAST($input, string $format): NodeList
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
     * @return Parser[]
     */
    public function getParsers(): array
    {
        return $this->parsers;
    }
}
