<?php

declare(strict_types=1);

namespace Isocontent\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use Composer\InstalledVersions;
use Isocontent\Bridge\Symfony\Bundle\DependencyInjection\IsocontentExtension;
use Isocontent\Isocontent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IsocontentExtensionTest extends TestCase
{
    public function test_it_loads()
    {
        if (PHP_VERSION_ID >= 80100 && version_compare(InstalledVersions::getVersion('symfony/config'), '5.0.0') < 0) {
            $this->markTestSkipped('Symfony 4 miss some return types');
        }

        $config = [];
        $containerBuilder = new ContainerBuilder;
        $extension = new IsocontentExtension;

        $extension->load($config, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition(Isocontent::class));
        $this->assertTrue($containerBuilder->hasAlias('isocontent'));

        $this->assertInstanceOf(Extension::class, $extension);
    }
}
