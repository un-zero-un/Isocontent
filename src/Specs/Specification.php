<?php

namespace Isocontent\Specs;

interface Specification
{
    /**
     * @param mixed $candidate
     *
     * @return bool
     */
    public function isSatisfiedBy($candidate): bool;

    public function and(Specification $specification): Specification;
}
