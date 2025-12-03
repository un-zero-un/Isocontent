<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;
use Isocontent\Exception\UnsupportedFormatException;

/**
 * A simple HTML parser using DOMDocument / LibXML.
 */
final class DOMParser implements Parser
{
    /**
     * @psalm-suppress MixedAssignment
     */
    #[\Override]
    public function parse(Builder $builder, mixed $input): void
    {
        if (!is_string($input)) {
            throw new UnsupportedFormatException();
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        if (!$input) {
            return;
        }

        $oldUseInternalErrors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);

        /** @var non-empty-string $html */
        $html = '<?xml encoding="UTF-8">'.$input;
        $document->loadHTML($html);

        libxml_clear_errors();
        libxml_use_internal_errors($oldUseInternalErrors);

        foreach ($document->getElementsByTagName('body') as $root) {
            assert($root instanceof \DOMElement);

            foreach ($root->childNodes as $childNode) {
                assert($childNode instanceof \DOMNode);
                $this->parseNode($builder, $childNode);
            }
        }
    }

    #[\Override]
    public function supportsFormat(string $format): bool
    {
        return 'html' === $format;
    }

    private function parseNode(Builder $builder, \DOMNode $node): void
    {
        switch ($node->nodeType) {
            case XML_TEXT_NODE:
                assert($node instanceof \DOMText);
                $builder->addTextNode(preg_replace('#\s{2,}#', ' ', $node->textContent) ?? '');

                return;

            case XML_ELEMENT_NODE:
                assert($node instanceof \DOMElement);
                $blockType = $this->parseBlockType($node);
                $childBuilder = $builder->addBlockNode($blockType[0], $blockType[1] ?? []);

                break;

            default:
                return;
        }

        if (0 === $node->childNodes->length) {
            return;
        }

        foreach ($node->childNodes as $subNode) {
            assert($subNode instanceof \DOMNode);
            $this->parseNode($childBuilder, $subNode);
        }
    }

    /**
     * @return array{0: string, 1?: array<string, scalar>}
     */
    private function parseBlockType(\DOMElement $node): array
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
                $nodeAttributes = $node->attributes;
                assert($nodeAttributes instanceof \DOMNamedNodeMap);
                $attributes = array_filter(['href' => $nodeAttributes->getNamedItem('href')?->nodeValue]);

                return ['link', $attributes];
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
