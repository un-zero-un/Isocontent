<?php

namespace Isocontent\Specs;

trait SpecificationImpl
{
    public function and(Specification $specification): Specification
    {
        return new AllMatch($this, $specification);
    }
}
