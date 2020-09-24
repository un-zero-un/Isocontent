<?php

declare(strict_types=1);

namespace Isocontent\Tests\Bridge\Symfony\Bundle;

use Isocontent\Bridge\Symfony\Bundle\IsocontentBundle;
use Isocontent\Parser\Parser;
use Isocontent\Renderer\Renderer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IsocontentBundleTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_builds(): void
    {
        $containerBuilder = $this->prophesize(ContainerBuilder::class);

        $parserChildConfiguration = $this->prophesize(ChildDefinition::class);
        $parserChildConfiguration->addTag('isocontent.parser')->shouldBeCalled();

        $rendererChildConfiguration = $this->prophesize(ChildDefinition::class);
        $rendererChildConfiguration->addTag('isocontent.renderer')->shouldBeCalled();

        $containerBuilder->registerForAutoconfiguration(Parser::class)->shouldBeCalled()->willReturn($parserChildConfiguration);
        $containerBuilder->registerForAutoconfiguration(Renderer::class)->shouldBeCalled()->willReturn($rendererChildConfiguration);

        $bundle = new IsocontentBundle;
        $bundle->build($containerBuilder->reveal());

        $this->assertInstanceOf(Bundle::class, $bundle);
    }
}
