<?php

namespace Isocontent\Parser;

use Dom\HTMLDocument;
use Dom\HTMLElement;
use Dom\NamedNodeMap;
use Dom\Node;
use Dom\Text;
use Isocontent\AST\Builder;
use Isocontent\Exception\FeatureNotAvailableException;

final class NativeDOMParser implements Parser
{
    public function supportsFormat(string $format): bool
    {
        return 'html' === $format && class_exists(HTMLDocument::class);
    }

    public function parse(Builder $builder, mixed $input): void
    {
        if (!class_exists(HTMLDocument::class)) {
            throw new FeatureNotAvailableException(HTMLDocument::class, __CLASS__); // @codeCoverageIgnore
        }

        $document = HTMLDocument::createEmpty();
        $body = $document->createElement('body');
        $document->appendChild($body);
        $body->innerHTML = $input;
        foreach ($body->childNodes as $childNode) {
            $this->parseNode($builder, $childNode);
        }
    }

    private function parseNode(Builder $builder, Node $node): void
    {
        switch ($node->nodeType) {
            case XML_TEXT_NODE:
                assert($node instanceof Text);
                $builder->addTextNode(preg_replace('#\s{2,}#', ' ', $node->textContent) ?? '');

                return;

            case XML_ELEMENT_NODE:
                assert($node instanceof HTMLElement);
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
            assert($subNode instanceof Node);
            $this->parseNode($childBuilder, $subNode);
        }
    }

    /**
     * @return array{0: string, 1?: array<string, scalar>}
     */
    private function parseBlockType(HTMLElement $node): array
    {
        switch (strtolower($node->nodeName)) {
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
                assert($nodeAttributes instanceof NamedNodeMap);
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
