<?php

declare(strict_types=1);

namespace Isocontent\Tests\Parser;

use Isocontent\AST\Builder;
use Isocontent\Parser\DOMParser;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class DOMParserTest extends TestCase
{
    use ProphecyTrait;

    public function testItSupportsHtml(): void
    {
        $this->assertTrue((new DOMParser())->supportsFormat('html'));
    }

    public function testItDoesNotSupportsOtherFormatThatHtml(): void
    {
        $this->assertFalse((new DOMParser())->supportsFormat('txt'));
        $this->assertFalse((new DOMParser())->supportsFormat('htm'));
    }

    public function testItParsesSimpleHtml(): void
    {
        $input = '<p><span>Paragraph text</span></p>';

        $builder = $this->prophesize(Builder::class);
        $pBuilder = $this->prophesize(Builder::class);
        $spanBuilder = $this->prophesize(Builder::class);
        $builder->addBlockNode('paragraph')->shouldBeCalled()->willReturn($pBuilder);
        $pBuilder->addBlockNode('inline_text')->shouldBeCalled()->willReturn($spanBuilder);
        $spanBuilder->addTextNode('Paragraph text')->shouldBeCalled()->willReturn($spanBuilder);

        $builder = $builder->reveal();
        (new DOMParser())->parse($builder, $input);
    }

    public function testItDoesNotThrowErrorWithEmptyHtml(): void
    {
        $builder = $this->prophesize(Builder::class);
        $builder->addTextNode(Argument::any())->shouldNotBeCalled();
        $builder->addBlockNode(Argument::any())->shouldNotBeCalled();

        (new DOMParser())->parse($builder->reveal(), '');
    }
}
