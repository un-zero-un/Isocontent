<?php

namespace Isocontent\Specs;

interface Specification
{
    /**
     * @param mixed $candidate
     */
    public function isSatisfiedBy($candidate): bool;

    public function and(Specification $specification): Specification;
}
