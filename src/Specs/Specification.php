<?php

namespace Isocontent\Specs;

interface Specification
{
    public function isSatisfiedBy(mixed $candidate): bool;

    public function and(Specification $specification): Specification;
}
