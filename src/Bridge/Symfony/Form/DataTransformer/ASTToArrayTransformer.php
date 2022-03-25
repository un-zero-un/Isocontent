<?php

namespace Isocontent\Bridge\Symfony\Form\DataTransformer;

use Isocontent\AST\NodeList;
use Isocontent\Isocontent;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ASTToArrayTransformer implements DataTransformerInterface
{
    private Isocontent $isocontent;

    public function __construct(Isocontent $isocontent)
    {
        $this->isocontent = $isocontent;
    }

    public function transform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof NodeList) {
            throw new TransformationFailedException;
        }

        return $value->toArray();
    }

    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException;
        }

        return $this->isocontent->buildAST($value, 'array');
    }
}
