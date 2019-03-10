<?php

declare(strict_types=1);

namespace Isocontent\Tests;

use Isocontent\AST\Builder;
use Isocontent\Exception\UnsupportedFormatException;
use Isocontent\Isocontent;
use Isocontent\Parser\Parser;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class IsocontentTest extends TestCase
{
    public function test_it_constructs_with_array(): void
    {
        $this->assertCount(
            2,
            (new Isocontent([
                $this->prophesize(Parser::class),
                $this->prophesize(Parser::class),
            ]))->getParsers()
        );
    }

    public function test_it_constructs_with_traversable(): void
    {
        $generator = function (): \Generator {
            yield $this->prophesize(Parser::class);
            yield $this->prophesize(Parser::class);
        };

        $this->assertCount(2, (new Isocontent($generator()))->getParsers());
    }

    public function test_it_builds_ast_from_parser(): void
    {
        $parser1 = $this->prophesize(Parser::class);
        $parser2 = $this->prophesize(Parser::class);

        $parser1->supportsFormat('html')->shouldBeCalled()->willReturn(false);
        $parser2->supportsFormat('html')->shouldBeCalled()->willReturn(true);

        $parser2->parse(Argument::type(Builder::class), 'foobar')->shouldBeCalled();

        (new Isocontent([$parser1->reveal(), $parser2->reveal()]))->buildAST('foobar', 'html');
    }

    public function test_it_screams_when_no_parser_matches_format(): void
    {
        $this->expectException(UnsupportedFormatException::class);

        $parser1 = $this->prophesize(Parser::class);
        $parser2 = $this->prophesize(Parser::class);

        $parser1->supportsFormat('html')->shouldBeCalled()->willReturn(false);
        $parser2->supportsFormat('html')->shouldBeCalled()->willReturn(false);

        (new Isocontent([$parser1->reveal(), $parser2->reveal()]))->buildAST('foobar', 'html');
    }
}
