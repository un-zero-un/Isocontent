<?php

namespace Isocontent\Tests\Exception;

use Isocontent\Exception\FeatureNotAvailableException;
use PHPUnit\Framework\TestCase;

final class FeatureNotAvailableExceptionTest extends TestCase
{
    public function testItTestsExceptionMessage(): void
    {
        $exception = new FeatureNotAvailableException('SomeFeature', 'SomeCaller');

        $this->assertSame(
            'The feature "SomeFeature" is not available in the current environment (needed by SomeCaller).',
            $exception->getMessage(),
        );
    }
}
