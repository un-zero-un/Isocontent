<?php

declare(strict_types=1);

namespace Isocontent\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use Isocontent\Bridge\Symfony\Bundle\DependencyInjection\IsocontentExtension;
use Isocontent\Isocontent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IsocontentExtensionTest extends TestCase
{
    public function testItLoads()
    {
        $config = [];
        $containerBuilder = new ContainerBuilder();
        $extension = new IsocontentExtension();

        $extension->load($config, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition(Isocontent::class));
        $this->assertTrue($containerBuilder->hasAlias('isocontent'));

        $this->assertInstanceOf(Extension::class, $extension);
    }
}
