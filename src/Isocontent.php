<?php

declare(strict_types=1);

namespace Isocontent;

use Isocontent\AST\Builder;
use Isocontent\AST\NodeList;
use Isocontent\Parser\Parser;

class Isocontent
{
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function buildAST($input): NodeList
    {
        $builder = Builder::create();
        $this->parser->parse($builder, $input);

        return $builder->getAST();
    }
}
