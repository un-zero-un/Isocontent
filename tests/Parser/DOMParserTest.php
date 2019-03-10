<?php

declare(strict_types=1);

namespace Isocontent\Tests\Parser;

use Isocontent\AST\Builder;
use Isocontent\Parser\DOMParser;
use PHPUnit\Framework\TestCase;

class DOMParserTest extends TestCase
{
    public function test_it_parses_simple_html(): void
    {
        $input = '<p><span>Paragraph text</span></p>';

        $builder = $this->prophesize(Builder::class);
        $pBuilder = $this->prophesize(Builder::class);
        $spanBuilder = $this->prophesize(Builder::class);
        $builder->addBlockNode('paragraph')->shouldBeCalled()->willReturn($pBuilder);
        $pBuilder->addBlockNode('inline_text')->shouldBeCalled()->willReturn($spanBuilder);
        $spanBuilder->addTextNode('Paragraph text')->shouldBeCalled()->willReturn($spanBuilder);

        $builder = $builder->reveal();
        (new DOMParser)->parse($builder, $input);
    }
}
