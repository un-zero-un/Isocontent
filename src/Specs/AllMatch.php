<?php

namespace Isocontent\Specs;

class AllMatch extends BaseSpecification
{
    /**
     * @var Specification[]
     */
    private $specifications;

    public function __construct(Specification ...$specifications)
    {
        $this->specifications = $specifications;
    }

    /**
     * @param mixed $candidate
     *
     * @return bool
     */
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
