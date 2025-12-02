<?php

declare(strict_types=1);

namespace Isocontent\Tests\AST;

use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class NodeListTest extends TestCase
{
    use ProphecyTrait;

    public function testItConstructsFromArray(): void
    {
        $node1 = $this->prophesize(Node::class);
        $node2 = $this->prophesize(Node::class);

        $node1->toArray()->shouldBeCalled()->willReturn(['type' => 'node1']);
        $node2->toArray()->shouldBeCalled()->willReturn(['type' => 'node2']);

        $nodeList = NodeList::fromArray([$node1->reveal(), $node2->reveal()]);
        $this->assertEquals(
            [
                ['type' => 'node1'],
                ['type' => 'node2'],
            ],
            $nodeList->toArray()
        );
    }
}
