<?php

declare(strict_types=1);

namespace Isocontent\Tests\AST;

use Isocontent\AST\Builder;
use Isocontent\AST\Node;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function test_it_adds_text_node(): void
    {
        $builder = Builder::create();
        $builder->addTextNode('foobar');

        $this->assertEquals([['type' => Node::TYPE_TEXT, 'value' => 'foobar']], $builder->getAST()->toArray());
    }

    public function test_it_adds_empty_block_node(): void
    {
        $builder = Builder::create();
        $builder->addBlockNode('paragraph');

        $this->assertEquals([['type' => Node::TYPE_BLOCK, 'block_type' => 'paragraph']], $builder->getAST()->toArray());
    }

    public function test_it_adds_non_empty_block_node(): void
    {
        $builder = Builder::create();
        $childBuilder = $builder->addBlockNode('paragraph');
        $childBuilder->addTextNode('foobar');

        $this->assertEquals(
            [
                [
                    'type' => Node::TYPE_BLOCK,
                    'block_type' => 'paragraph',
                    'children' => [
                        ['type' => Node::TYPE_TEXT, 'value' => 'foobar']
                    ],
                ]
            ],
            $builder->getAST()->toArray()
        );
    }
}
