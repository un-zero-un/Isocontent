<?php

declare(strict_types=1);

namespace Isocontent\Bridge\Symfony\Form\DataTransformer;

use Isocontent\AST\Node;
use Isocontent\AST\NodeList;
use Isocontent\Isocontent;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<Node|NodeList, string>
 */
final class ASTToStringTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly Isocontent $isocontent,
        private readonly string $format,
    ) {
    }

    #[\Override]
    public function transform($value): ?string
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof NodeList) {
            throw new TransformationFailedException();
        }

        $result = $this->isocontent->render($value, $this->format);
        assert(is_string($result));

        return $result;
    }

    /**
     * @psalm-suppress DocblockTypeContradiction
     */
    #[\Override]
    public function reverseTransform($value): Node|NodeList|null
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException();
        }

        return $this->isocontent->buildAST($value, $this->format);
    }
}
