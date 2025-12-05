<?php

namespace Isocontent\Tests\Bridge\Symfony\Form\DataTransformer;

use Isocontent\AST\NodeList;
use Isocontent\Bridge\Symfony\Form\DataTransformer\ASTToArrayTransformer;
use Isocontent\Isocontent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class ASTToArrayTransformerTest extends TestCase
{
    private Isocontent&MockObject $isocontent;

    private ASTToArrayTransformer $transformer;

    protected function setUp(): void
    {
        $this->isocontent = $this->createMock(Isocontent::class);
        $this->transformer = new ASTToArrayTransformer($this->isocontent);
    }

    public function testTransform(): void
    {
        $nodeList = $this->createMock(NodeList::class);
        $nodeList->expects($this->once())
            ->method('toArray')
            ->willReturn(['test' => 'value']);

        $result = $this->transformer->transform($nodeList);

        $this->assertSame(['test' => 'value'], $result);
    }

    public function testItThrowsIfTransformArgumentIsNotANodeList(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->transform(new \stdClass());
    }

    public function testTransformBypassNullValue(): void
    {
        $result = $this->transformer->transform(null);

        $this->assertNull($result);
    }

    public function testReverseTransformBypassNullValue(): void
    {
        $result = $this->transformer->reverseTransform(null);

        $this->assertNull($result);
    }

    public function testItThrowsIfReverseTransformArgumentIsNotAnArray(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer->reverseTransform('not an array');
    }

    public function testReverseTransform(): void
    {
        $nodeList = $this->createMock(NodeList::class);
        $this->isocontent->expects($this->once())
            ->method('buildAST')
            ->with(['test' => 'value'], 'array')
            ->willReturn($nodeList);

        $result = $this->transformer->reverseTransform(['test' => 'value']);

        $this->assertSame($nodeList, $result);
    }
}
