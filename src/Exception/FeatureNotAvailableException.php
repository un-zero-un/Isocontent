<?php

namespace Isocontent\Exception;

final class FeatureNotAvailableException extends \RuntimeException implements Exception
{
    public function __construct(string $feature, string $caller)
    {
        parent::__construct(
            sprintf(
                'The feature "%s" is not available in the current environment (needed by %s).',
                $feature,
                $caller,
            ),
        );
    }
}
