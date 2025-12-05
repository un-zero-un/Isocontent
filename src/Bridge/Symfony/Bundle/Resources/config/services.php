<?php

use Isocontent\Isocontent;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->private()
    ;

    $container->services()
        ->load('Isocontent\\', __DIR__.'/../../../../../')
        ->exclude('../../{DependencyInjection,IsocontentBundle.php,Resources}')
    ;

    $container->services()
        ->set(Isocontent::class)
        ->arg('$parsers', tagged_iterator('isocontent.parser'))
        ->arg('$renderers', tagged_iterator('isocontent.renderer'))
    ;

    $container->services()
        ->alias('isocontent', Isocontent::class);
};
