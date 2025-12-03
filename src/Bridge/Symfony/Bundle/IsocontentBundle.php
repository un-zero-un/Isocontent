<?php

declare(strict_types=1);

namespace Isocontent\Bridge\Symfony\Bundle;

use Isocontent\Parser\Parser;
use Isocontent\Renderer\Renderer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class IsocontentBundle extends Bundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(Parser::class)->addTag('isocontent.parser');
        $container->registerForAutoconfiguration(Renderer::class)->addTag('isocontent.renderer');
    }
}
