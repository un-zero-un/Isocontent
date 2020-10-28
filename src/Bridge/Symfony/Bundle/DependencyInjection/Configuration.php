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
        $treeBuilder = new TreeBuilder('isocontent');
        // @codeCoverageIgnoreStart
        if (method_exists($treeBuilder, 'root')) {
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->root('isocontent');
        } else {
            /** @var ArrayNodeDefinition $rootNode */
            $rootNode = $treeBuilder->getRootNode();
        }
        // @codeCoverageIgnoreEnd

        $rootNode
            ->children()
            ->end();

        return $treeBuilder;
    }
}
