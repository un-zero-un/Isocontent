<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;

final class DOMParser implements Parser
{
    public function parse(Builder $builder, $input): void
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        if (!$input) {
            return;
        }

        $oldUseInternalErrors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);

        $document->loadHTML('<?xml encoding="UTF-8">'.$input);

        libxml_clear_errors();
        libxml_use_internal_errors($oldUseInternalErrors);

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
     * @return array{0: string, 1?: array<string, scalar>}
     */
    private function parseBlockType(\DOMNode $node): array
    {
        switch ($node->nodeName) {
            case 'h1':
                return ['title', ['level' => 1]];

            case 'h2':
                return ['title', ['level' => 2]];

            case 'h3':
                return ['title', ['level' => 3]];

            case 'h4':
                return ['title', ['level' => 4]];

            case 'h5':
                return ['title', ['level' => 5]];

            case 'h6':
                return ['title', ['level' => 6]];

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

            case 'a':
                return [
                    'link', [
                        'href' => (
                            null !== $node->attributes && null !== $node->attributes->getNamedItem('href')
                                ? $node->attributes->getNamedItem('href')->nodeValue
                                : null
                        ),
                    ],
                ];

            case 'del':
                return ['stripped'];

            case 'hr':
                return ['separator'];

            case 'sub':
                return ['subscript'];

            case 'sup':
                return ['superscript'];

            case 'code':
                return ['code'];

            default:
                return ['generic'];
        }
    }
}
