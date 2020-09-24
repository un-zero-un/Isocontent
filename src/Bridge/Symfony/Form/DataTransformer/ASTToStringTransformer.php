<?php

declare(strict_types=1);

namespace Isocontent\Bridge\Symfony\Form\DataTransformer;

use Isocontent\AST\NodeList;
use Isocontent\Isocontent;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ASTToStringTransformer implements DataTransformerInterface
{
    private Isocontent $isocontent;

    private string $format;

    public function __construct(Isocontent $isocontent, string $format)
    {
        $this->isocontent = $isocontent;
        $this->format     = $format;
    }

    public function transform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof NodeList) {
            throw new TransformationFailedException;
        }

        return $this->isocontent->render($value, $this->format);
    }

    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException;
        }

        return $this->isocontent->buildAST($value, $this->format);
    }
}
