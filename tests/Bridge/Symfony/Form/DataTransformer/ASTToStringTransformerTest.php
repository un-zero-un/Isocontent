<?php

declare(strict_types=1);

namespace Isocontent\Tests\Bridge\Symfony\Form\DataTransformer;

use Isocontent\AST\NodeList;
use Isocontent\Bridge\Symfony\Form\DataTransformer\ASTToStringTransformer;
use Isocontent\Isocontent;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ASTToStringTransformerTest extends TestCase
{
    use ProphecyTrait;

    private $isocontent;

    protected function setUp(): void
    {
        $this->isocontent = $this->prophesize(Isocontent::class);
    }

    public function testItAcceptsNullValue()
    {
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');
        $this->assertNull($transformer->transform(null));
        $this->assertNull($transformer->reverseTransform(null));
        $this->assertNull($transformer->reverseTransform(''));
    }

    public function testItTransforms()
    {
        $nodeList = $this->prophesize(NodeList::class);
        $this->isocontent->render($nodeList, 'html')->shouldBeCalled()->willReturn('value');
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');

        $this->assertSame('value', $transformer->transform($nodeList->reveal()));
    }

    public function testTransformException()
    {
        $this->isocontent->render(Argument::any())->shouldNotBeCalled();
        $this->isocontent->buildAST(Argument::any())->shouldNotBeCalled();
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');

        $this->expectException(TransformationFailedException::class);

        $transformer->transform('test');
    }

    public function testItReverseTransforms()
    {
        $nodeList = $this->prophesize(NodeList::class);
        $this->isocontent->buildAST('value', 'html')->shouldBeCalled()->willReturn($nodeList);
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');

        $this->assertSame($nodeList->reveal(), $transformer->reverseTransform('value'));
    }

    public function testReverseTransformException()
    {
        $this->isocontent->render(Argument::any())->shouldNotBeCalled();
        $this->isocontent->buildAST(Argument::any())->shouldNotBeCalled();
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');

        $this->expectException(TransformationFailedException::class);

        $transformer->reverseTransform(34);
    }
}
