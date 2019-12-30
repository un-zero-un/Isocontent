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
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $builder = new TreeBuilder('isocontent');

            /** @var ArrayNodeDefinition $rootNode */
            $root = $builder->getRootNode();
        } else {
            $builder = new TreeBuilder();

            /** @var ArrayNodeDefinition $rootNode */
            $root= $builder->root('isocontent');
        }

        $root
            ->children()
            ->end();

        return $builder;
    }
}
