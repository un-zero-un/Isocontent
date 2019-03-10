<?php

declare(strict_types=1);

namespace Isocontent\Tests\AST;

use Isocontent\AST\BlockNode;
use Isocontent\AST\NodeList;
use PHPUnit\Framework\TestCase;

class BlockNodeTest extends TestCase
{
    public function test_it_renders_as_array(): void
    {
        $emptyBlockNode = BlockNode::fromBlockType('foo');
        $this->assertEquals(['type' => 'block', 'block_type' => 'foo'], $emptyBlockNode->toArray());


        $nodeList = $this->prophesize(NodeList::class);
        $nodeList->toArray()->shouldBeCalled()->willReturn(['type' => 'text', 'value' => 'foobar']);
        $filledBlockNode = BlockNode::fromBlockType('foo', $nodeList->reveal());

        $this->assertSame('block', $filledBlockNode->getType());
        $this->assertSame('foo', $filledBlockNode->getBlockType());
        $this->assertInstanceOf(NodeList::class, $filledBlockNode->getChildren());
        $this->assertEquals(
            ['type' => 'block', 'block_type' => 'foo', 'children' => ['type' => 'text', 'value' => 'foobar']],
            $filledBlockNode->toArray()
        );
    }
}
