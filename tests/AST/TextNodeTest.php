<?php

declare(strict_types=1);

namespace Isocontent\Tests\AST;

use Isocontent\AST\TextNode;
use PHPUnit\Framework\TestCase;

class TextNodeTest extends TestCase
{
    public function test_it_renders_as_array(): void
    {
        $node = TextNode::fromText('foobar');

        $this->assertSame('text', $node->getType());
        $this->assertSame('foobar', $node->getValue());
        $this->assertEquals(['type' => 'text', 'value' => 'foobar'], $node->toArray());
    }
}
