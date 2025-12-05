<?php

declare(strict_types=1);

namespace Isocontent\AST;

final class TextNode implements Node
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    #[\Override]
    public function getType(): string
    {
        return Node::TYPE_TEXT;
    }

    /**
     * @return array{type: string, value: string}
     */
    #[\Override]
    public function toArray(): array
    {
        return ['type' => $this->getType(), 'value' => $this->value];
    }

    public static function fromText(string $text): self
    {
        return new self($text);
    }
}
