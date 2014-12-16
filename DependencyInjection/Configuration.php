<?php

namespace UBC\LtCommonsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $rootNode = $treeBuilder->root('lt_commons');

        $this->addProvidersSection($rootNode);

        return $treeBuilder;
    }

    public function addProvidersSection(ArrayNodeDefinition $rootNode)
    {
        $providerNodeBuilder = $rootNode
            ->fixXmlConfig('provider')
            ->children()
                ->arrayNode('providers')
                    ->example(array(
                        'sis' => array(
                            'base_url' => 'http://sisapi.example.com',
                            'http_client' => 'Guzzle',
                            'auth' => array(
                                'module' => 'Auth2',
                                'rpc_path' => '/auth/rpc',
                                'username' => 'service_username',
                                'password' => 'service_password',
                                'service_application' => 'service_app',
                                'service_url' => 'https://www.auth.stg.id.ubc.ca'
                            ),
                            'serializer' => 'JMS'
                        )))
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('class')
                    ->prototype('array')
        ;

        $providerNodeBuilder
            ->children()
                ->scalarNode('base_url')->end()
                ->scalarNode('http_client')->defaultValue('Guzzle')->end()
                ->scalarNode('serializer')->defaultValue('JMS')->end()
                ->arrayNode('auth')
                    ->children()
                        ->scalarNode('module')->defaultValue('HttpBasic')->end()
                        ->scalarNode('rpc_path')->defaultValue('/auth/rpc')->end()
                        ->scalarNode('username')->isRequired()->end()
                        ->scalarNode('password')->isRequired()->end()
                        ->scalarNode('service_application')->end()
                        ->scalarNode('service_url')->defaultValue('https://www.auth.stg.id.ubc.ca')->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function ($v) { return 'Auth2' === $v['module'] && empty($v['service_application']);})
                        ->thenInvalid('The service_application name has to be specified to use Auth2 module.')
                    ->end()
                ->end()
                ->scalarNode('path')->end()
            ->end()
        ;

        $providerNodeBuilder
            ->end()
            ->validate()
                ->ifTrue(function ($v) { return isset($v['sis']) && empty($v['sis']['base_url']); })
                ->thenInvalid('The base_url has to specified to use sis data provider.')
            ->end()
            ->validate()
                ->ifTrue(function ($v) { return isset($v['xml']) && empty($v['xml']['path']); })
                ->thenInvalid('The base_url has to specified to use xml data provider.')
            ->end()
            ->end()
        ;
    }
}
