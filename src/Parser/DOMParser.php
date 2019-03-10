<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;

final class DOMParser implements Parser
{
    public function parse(Builder $builder, $input): void
    {
        $document = new \DOMDocument('1.0', 'utf-8');
        $document->loadHTML($input);
        $root = $document->getElementsByTagName('body')->item(0);

        if (null === $root) {
            return;
        }

        foreach ($root->childNodes as $childNode) {
            $this->parseNode($builder, $childNode);
        }
    }

    private function parseNode(Builder $builder, \DOMNode $node): void
    {
        $childBuilder = null;
        switch ($node->nodeType) {
            case XML_TEXT_NODE:
                $builder->addTextNode($node->textContent);
                return;
            case XML_ELEMENT_NODE:
                $childBuilder = $builder->addBlockNode($this->parseBlockType($node));
                break;
            default:
                \dump($node);
        }

        if (null === $childBuilder || null === $node->childNodes) {
            return;
        }

        foreach ($node->childNodes as $subNode) {
            $this->parseNode($childBuilder, $subNode);
        }
    }

    private function parseBlockType(\DOMNode $node)
    {
        switch ($node->nodeName) {
            case 'p':
                return 'paragraph';
            case 'span':
                return 'inline_text';
            default:
                return 'generic';
        }
    }
}
