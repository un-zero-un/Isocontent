<?php

namespace Isocontent\Specs;

/**
 * @method isSatisfiedBy($candidate)
 */
interface Specification
{
    public function and(Specification $specification): Specification;
}
