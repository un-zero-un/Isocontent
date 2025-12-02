<?php

declare(strict_types=1);

namespace Isocontent\Tests;

use Isocontent\AST\Builder;
use Isocontent\AST\NodeList;
use Isocontent\Exception\UnsupportedFormatException;
use Isocontent\Isocontent;
use Isocontent\Parser\Parser;
use Isocontent\Renderer\Renderer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class IsocontentTest extends TestCase
{
    use ProphecyTrait;

    public function testItConstructsWithParsersArray(): void
    {
        $this->assertCount(
            2,
            (new Isocontent([
                $this->prophesize(Parser::class),
                $this->prophesize(Parser::class),
            ], []))->getParsers()
        );
    }

    public function testItConstructsWithParsersTraversable(): void
    {
        $generator = function (): \Generator {
            yield $this->prophesize(Parser::class);

            yield $this->prophesize(Parser::class);
        };

        $this->assertCount(2, (new Isocontent($generator(), []))->getParsers());
    }

    public function testItBuildsAstFromParser(): void
    {
        $parser1 = $this->prophesize(Parser::class);
        $parser2 = $this->prophesize(Parser::class);

        $parser1->supportsFormat('html')->shouldBeCalled()->willReturn(false);
        $parser2->supportsFormat('html')->shouldBeCalled()->willReturn(true);

        $parser2->parse(Argument::type(Builder::class), 'foobar')->shouldBeCalled();

        (new Isocontent([$parser1->reveal(), $parser2->reveal()], []))->buildAST('foobar', 'html');
    }

    public function testItScreamsWhenNoParserMatchesFormat(): void
    {
        $this->expectException(UnsupportedFormatException::class);

        $parser1 = $this->prophesize(Parser::class);
        $parser2 = $this->prophesize(Parser::class);

        $parser1->supportsFormat('html')->shouldBeCalled()->willReturn(false);
        $parser2->supportsFormat('html')->shouldBeCalled()->willReturn(false);

        (new Isocontent([$parser1->reveal(), $parser2->reveal()], []))->buildAST('foobar', 'html');
    }

    public function testItConstructsWithRenderersArray(): void
    {
        $this->assertCount(
            2,
            (new Isocontent([], [
                $this->prophesize(Renderer::class),
                $this->prophesize(Renderer::class),
            ]))->getRenderers()
        );
    }

    public function testItConstructsWithRenderersTraversable(): void
    {
        $generator = function (): \Generator {
            yield $this->prophesize(Renderer::class);

            yield $this->prophesize(Renderer::class);
        };

        $this->assertCount(2, (new Isocontent([], $generator()))->getRenderers());
    }

    public function testItRendersFromAst(): void
    {
        $renderer1 = $this->prophesize(Renderer::class);
        $renderer2 = $this->prophesize(Renderer::class);

        $renderer1->supportsFormat('json')->shouldBeCalled()->willReturn(false);
        $renderer2->supportsFormat('json')->shouldBeCalled()->willReturn(true);

        $ast = $this->prophesize(NodeList::class);
        $renderer2->render($ast)->shouldBeCalled()->willReturn('rendered_ast');

        $this->assertSame(
            'rendered_ast',
            (new Isocontent([], [$renderer1->reveal(), $renderer2->reveal()]))->render($ast->reveal(), 'json')
        );
    }

    public function testItScreamsWhenNoRenderersMatchesFormat(): void
    {
        $this->expectException(UnsupportedFormatException::class);

        $renderer1 = $this->prophesize(Renderer::class);
        $renderer2 = $this->prophesize(Renderer::class);

        $renderer1->supportsFormat('json')->shouldBeCalled()->willReturn(false);
        $renderer2->supportsFormat('json')->shouldBeCalled()->willReturn(false);

        $ast = $this->prophesize(NodeList::class);
        (new Isocontent([], [$renderer1->reveal(), $renderer2->reveal()]))->render($ast->reveal(), 'json');
    }
}
