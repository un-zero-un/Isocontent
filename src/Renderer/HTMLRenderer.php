<?php

declare(strict_types=1);

namespace Isocontent\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Specs\BlockArgumentMatch;
use Isocontent\Specs\BlockTypeMatch;

class HTMLRenderer implements Renderer
{
    /**
     * @var array<array>
     */
    private $tags;

    public function __construct(?array $tags = null)
    {
        $this->tags = $tags ?: [
            [new BlockTypeMatch('paragraph'), 'p'],
            [new BlockTypeMatch('inline_text'), 'span'],
            [new BlockTypeMatch('emphasis'), 'em'],
            [new BlockTypeMatch('strong'), 'strong'],
            [new BlockTypeMatch('generic'), 'span'],
            [(new BlockTypeMatch('list'))->and(new BlockArgumentMatch('ordered', false)), 'ul'],
            [(new BlockTypeMatch('list'))->and(new BlockArgumentMatch('ordered', true)), 'ol'],
            [new BlockTypeMatch('list_item'), 'li'],
            [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 4)), 'h4'],
            [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 5)), 'h5'],
            [new BlockTypeMatch('quote'), 'blockquote'],
            [new BlockTypeMatch('new_line'), 'br'],
        ];
    }

    public function render(NodeList $ast)
    {
        return array_reduce(
            $ast->getNodes(),
            function (string $memo, Node $node) {
                if ($node instanceof TextNode) {
                    return $memo . $node->getValue();
                }

                if ($node instanceof BlockNode) {
                    return $memo . $this->renderBlockNode($node);
                }

                return $memo;
            },
            ''
        );
    }

    public function supportsFormat(string $format): bool
    {
        return 'html' === $format;
    }

    private function renderBlockNode(BlockNode $blockNode): string
    {
        $tagName = 'span';
        foreach ($this->tags as $tag) {
            if ($tag[0]->isSatisfiedBy($blockNode)) {
                $tagName = $tag[1];
            }
        }

        if (null === $blockNode->getChildren()) {
            return strtr('<:tagName: />', [':tagName:' => $tagName]);
        }

        return strtr(
            '<:tagName:>:content:</:tagName:>',
            [
                ':tagName:' => $tagName,
                ':content:' => $this->render($blockNode->getChildren()),
            ]
        );
    }
}
