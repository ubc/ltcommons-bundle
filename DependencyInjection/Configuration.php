<?php

namespace UBC\SISAPIBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sisapi');

        $rootNode
            ->children()
            ->append($this->addAuth2Node())
            ->append($this->addSISNode())
            ->end()
        ;

        return $treeBuilder;
    }

    public function addAuth2Node()
    {

        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('Auth2');

        $node
            ->children()
            ->scalarNode('username')
            ->isRequired()
            ->end()
            ->scalarNode('password')
            ->isRequired()
            ->end()
            ->scalarNode('service_application')
            ->isRequired()
            ->end()
            ->scalarNode('service_url')
            ->isRequired()
            ->end()
            ->end()
        ;

        return $node;
    }

    public function addSISNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('SIS');

        $node
            ->children()
            ->scalarNode('base_url')
            ->isRequired()
            ->end()
            ->end()
        ;

        return $node;
    }
}
