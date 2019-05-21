<?php

namespace Isocontent\Specs;

class AllMatch implements Specification
{
    use SpecificationImpl;

    /**
     * @var Specification[]
     */
    private $specifications;

    public function __construct(Specification ...$specifications)
    {
        $this->specifications = $specifications;
    }

    public function isSatisfiedBy($candidate): bool
    {
        foreach ($this->specifications as $specification) {
            if (!$specification->isSatisfiedBy($candidate)) {
                return false;
            }
        }

        return true;
    }
}
