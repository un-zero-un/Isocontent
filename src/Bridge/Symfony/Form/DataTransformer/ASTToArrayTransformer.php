<?php

namespace Isocontent\Bridge\Symfony\Form\DataTransformer;

use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\Isocontent;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<NodeList|Node, array>
 */
final class ASTToArrayTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly Isocontent $isocontent,
    ) {
    }

    #[\Override]
    public function transform($value): ?array
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof NodeList) {
            throw new TransformationFailedException();
        }

        return $value->toArray();
    }

    /**
     * @psalm-suppress DocblockTypeContradiction
     */
    #[\Override]
    public function reverseTransform($value): NodeList|Node|null
    {
        if (null === $value) {
            return null;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException();
        }

        return $this->isocontent->buildAST($value, 'array');
    }
}
