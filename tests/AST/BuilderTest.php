<?php

declare(strict_types=1);

namespace Isocontent\Tests\AST;

use Isocontent\AST\Builder;
use Isocontent\AST\Node;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testItAddsTextNode(): void
    {
        $builder = Builder::create();
        $builder->addTextNode('foobar');

        $this->assertEquals([['type' => Node::TYPE_TEXT, 'value' => 'foobar']], $builder->getAST()->toArray());
    }

    public function testItAddsEmptyBlockNode(): void
    {
        $builder = Builder::create();
        $builder->addBlockNode('paragraph');

        $this->assertEquals(
            [['type' => Node::TYPE_BLOCK, 'block_type' => 'paragraph', 'arguments' => []]],
            $builder->getAST()->toArray(),
        );
    }

    public function testItAddsNonEmptyBlockNode(): void
    {
        $builder = Builder::create();
        $childBuilder = $builder->addBlockNode('paragraph');
        $childBuilder->addTextNode('foobar');

        $this->assertEquals(
            [
                [
                    'type' => Node::TYPE_BLOCK,
                    'block_type' => 'paragraph',
                    'arguments' => [],
                    'children' => [
                        ['type' => Node::TYPE_TEXT, 'value' => 'foobar'],
                    ],
                ],
            ],
            $builder->getAST()->toArray()
        );
    }
}
