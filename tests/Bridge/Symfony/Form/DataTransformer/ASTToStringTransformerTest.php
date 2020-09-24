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

    public function __construct()
    {
        $this->isocontent = $this->prophesize(Isocontent::class);

        parent::__construct();
    }

    public function test_it_accepts_null_value()
    {
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');
        $this->assertNull($transformer->transform(null));
        $this->assertNull($transformer->reverseTransform(null));
        $this->assertNull($transformer->reverseTransform(''));
    }

    public function test_it_transforms()
    {
        $nodeList = $this->prophesize(NodeList::class);
        $this->isocontent->render($nodeList, 'html')->shouldBeCalled()->willReturn('value');
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');

        $this->assertSame('value', $transformer->transform($nodeList->reveal()));
    }

    public function test_transform_exception()
    {
        $this->isocontent->render(Argument::any())->shouldNotBeCalled();
        $this->isocontent->buildAST(Argument::any())->shouldNotBeCalled();
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');

        $this->expectException(TransformationFailedException::class);

        $transformer->transform('test');
    }

    public function test_it_reverse_transforms()
    {
        $nodeList = $this->prophesize(NodeList::class);
        $this->isocontent->buildAST('value', 'html')->shouldBeCalled()->willReturn($nodeList);
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');

        $this->assertSame($nodeList->reveal(), $transformer->reverseTransform('value'));
    }

    public function test_reverse_transform_exception()
    {
        $this->isocontent->render(Argument::any())->shouldNotBeCalled();
        $this->isocontent->buildAST(Argument::any())->shouldNotBeCalled();
        $transformer = new ASTToStringTransformer($this->isocontent->reveal(), 'html');

        $this->expectException(TransformationFailedException::class);

        $transformer->reverseTransform(34);
    }
}
