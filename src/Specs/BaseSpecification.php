<?php

namespace Isocontent\Specs;

abstract class BaseSpecification implements Specification
{
    public function and(Specification $specification): Specification
    {
        return new AllMatch($this, $specification);
    }
}
