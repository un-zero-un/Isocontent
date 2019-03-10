<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;

final class DOMParser implements Parser
{
    public function parse(Builder $builder, $input): void
    {
        $document = new \DOMDocument('1.0', 'utf-8');
        if (!$input) {
            return;
        }

        $document->loadHTML($input);
        foreach ($document->getElementsByTagName('body') as $root) {
            foreach ($root->childNodes as $childNode) {
                $this->parseNode($builder, $childNode);
            }
        }
    }

    public function supportsFormat(string $format): bool
    {
        return 'html' === $format;
    }

    private function parseNode(Builder $builder, \DOMNode $node): void
    {
        $childBuilder = null;

        switch ($node->nodeType) {
            case XML_TEXT_NODE:
                $builder->addTextNode(preg_replace('#\s{2,}#', ' ', $node->textContent) ?: '');

                return;
            case XML_ELEMENT_NODE:
                $childBuilder = $builder->addBlockNode($this->parseBlockType($node));
                break;
            default:
                return;
        }

        if (null === $node->childNodes || 0 === count($node->childNodes)) {
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
            case 'em':
                return 'emphasis';
            case 'strong':
                return 'strong';
            case 'span':
                return 'inline_text';
            default:
                return 'generic';
        }
    }
}
