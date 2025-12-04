<?php

declare(strict_types=1);

namespace Isocontent\Parser;

use Isocontent\AST\Builder;
use Isocontent\AST\Node;
use Isocontent\Exception\UnsupportedFormatException;

final class ArrayParser implements Parser
{
    #[\Override]
    public function parse(Builder $builder, mixed $input): void
    {
        if (!\is_array($input)) {
            throw new UnsupportedFormatException();
        }

        if (isset($input['type'])) {
            $this->parseNode($builder, $input);

            return;
        }

        /** @var array $node */
        foreach ($input as $node) {
            $this->parseNode($builder, $node);
        }
    }

    #[\Override]
    public function supportsFormat(string $format): bool
    {
        return 'array' === $format;
    }

    private function parseNode(Builder $builder, array $node): void
    {
        if (Node::TYPE_TEXT === $node['type']) {
            assert(is_string($node['value']));
            $builder->addTextNode($node['value']);

            return;
        }

        /**
         * @var array{
         *     type: 'block',
         *     block_type:  string,
         *     arguments?: array<string, scalar>,
         *     children?: array<array>,
         * } $node
         * */
        $childNodes = $node['children'] ?? null;
        $blockType = $node['block_type'];

        $childBuilder = $builder->addBlockNode($blockType, $node['arguments'] ?? []);

        if (null !== $childNodes) {
            foreach ($childNodes as $childNode) {
                $this->parseNode($childBuilder, $childNode);
            }
        }
    }
}
