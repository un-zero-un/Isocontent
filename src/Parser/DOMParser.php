<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;

final class DOMParser implements Parser
{
    /**
     * @param Builder $builder
     * @param mixed   $input
     */
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
                $childBuilder = $builder->addBlockNode(...$this->parseBlockType($node));
                break;
            default:
                return;
        }

        if (null === $node->childNodes || 0 === $node->childNodes->length) {
            return;
        }

        foreach ($node->childNodes as $subNode) {
            $this->parseNode($childBuilder, $subNode);
        }
    }

    /**
     * @param \DOMNode $node
     *
     * @return array{0: string, 1?: array<string, scalar>}
     */
    private function parseBlockType(\DOMNode $node): array
    {
        switch ($node->nodeName) {
            case 'h4':
                return ['title', ['level' => 4]];
            case 'h5':
                return ['title', ['level' => 5]];
            case 'p':
                return ['paragraph'];
            case 'em':
                return ['emphasis'];
            case 'strong':
                return ['strong'];
            case 'span':
                return ['inline_text'];
            case 'ul':
                return ['list', ['ordered' => false]];
            case 'ol':
                return ['list', ['ordered' => true]];
            case 'li':
                return ['list_item'];
            case 'blockquote':
                return ['quote'];
            case 'br':
                return ['new_line'];
            default:
                return ['generic'];
        }
    }
}
