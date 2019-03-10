<?php

declare(strict_types=1);

namespace Isocontent\Bridge\Symfony\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $root */
        $root = $builder->root('isocontent');

        $root
            ->children()
            ->end();

        return $builder;
    }
}
