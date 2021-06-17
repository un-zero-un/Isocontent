<?php

declare(strict_types=1);

namespace Isocontent\Renderer;

use Isocontent\AST\BlockNode;
use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\AST\TextNode;
use Isocontent\Specs\BlockArgumentMatch;
use Isocontent\Specs\BlockTypeMatch;
use Isocontent\Specs\Specification;

class HTMLRenderer implements Renderer
{
    /**
     * @var array<array{Specification, string}>
     */
    private array $tags;

    /**
     * @param array<array{Specification, string}> $tags
     */
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
            [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 1)), 'h1'],
            [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 2)), 'h2'],
            [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 3)), 'h3'],
            [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 4)), 'h4'],
            [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 5)), 'h5'],
            [(new BlockTypeMatch('title'))->and(new BlockArgumentMatch('level', 6)), 'h6'],
            [new BlockTypeMatch('quote'), 'blockquote'],
            [new BlockTypeMatch('new_line'), 'br'],
            [new BlockTypeMatch('link'), 'a'],
            [new BlockTypeMatch('striped'), 'del'],
            [new BlockTypeMatch('separator'), 'hr'],
            [new BlockTypeMatch('subscript'), 'sub'],
            [new BlockTypeMatch('superscript'), 'sup'],
            [new BlockTypeMatch('code'), 'code'],
        ];
    }

    public function render(NodeList $ast): string
    {
        return array_reduce(
            $ast->getNodes(),
            function (string $memo, Node $node) {
                if ($node instanceof TextNode) {
                    return $memo . htmlentities($node->getValue());
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

        $arguments = '';
        if (count($blockNode->getArguments()) > 0 && array_key_exists('arguments', $blockNode->getArguments()) && count($blockNode->getArguments()['arguments']) > 0) {
            $args = [];
            foreach($blockNode->getArguments()['arguments'] as $k => $v) {
                $args[] = sprintf('%s="%s"', $k, $v);
            }

            $arguments = ' ' . implode(' ', $args);
        }

        if (null === $blockNode->getChildren()) {
            return strtr('<:tagName::arguments: />', [':tagName:' => $tagName, ':arguments:' => $arguments]);
        }

        return strtr(
            '<:tagName::arguments:>:content:</:tagName:>',
            [
                ':tagName:' => $tagName,
                ':content:' => $this->render($blockNode->getChildren()),
                ':arguments:' => $arguments,
            ]
        );
    }
}
