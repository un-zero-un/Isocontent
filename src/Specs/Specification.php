<?php

namespace Isocontent\Specs;

interface Specification
{
    public function isSatisfiedBy($candidate): bool;

    public function and(Specification $specification): Specification;
}
