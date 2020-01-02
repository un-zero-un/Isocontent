<?php

declare(strict_types=1);

namespace Isocontent\Tests\Parser;

use Isocontent\AST\Builder;
use Isocontent\Parser\DOMParser;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DOMParserTest extends TestCase
{
    public function test_it_supports_html(): void
    {
        $this->assertTrue((new DOMParser)->supportsFormat('html'));
    }

    public function test_it_does_not_supports_other_format_that_html(): void
    {
        $this->assertFalse((new DOMParser)->supportsFormat('txt'));
        $this->assertFalse((new DOMParser)->supportsFormat('htm'));
    }

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

    public function test_it_does_not_throw_error_with_empty_html(): void
    {
        $builder = $this->prophesize(Builder::class);
        $builder->addTextNode(Argument::any())->shouldNotBeCalled();
        $builder->addBlockNode(Argument::any())->shouldNotBeCalled();

        (new DOMParser)->parse($builder->reveal(), '');
    }
}
