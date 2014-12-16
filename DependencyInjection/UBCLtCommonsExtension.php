<?php

namespace UBC\LtCommonsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * Class UBCLtCommonsExtension Dependency inject extension for LT Commons. This extension helps to
 * 1. Load the service definition file, services.yml
 * 2. Valid configuration
 * 3. Merge configuration file into default values in services.yml
 * @package UBC\LtCommons
 */
class UBCLtCommonsExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $configs   An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $mainConfig = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($mainConfig, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if ($config['providers']) {
            $this->createDataProviders($config['providers'], $container);
        }
    }

    /**
     * Create Data Provider definitions
     *
     * @param array            $providers
     * @param ContainerBuilder $container
     */
    private function createDataProviders($providers, ContainerBuilder $container)
    {
        $providerIds = array();
        foreach ($providers as $class => $provider) {
            $providerIds[] = $this->createDataProvider($class, $provider, $container);
        }

        $container->getDefinition('ubc_lt_commons.provider_factory.generic')
            ->setArguments(array($providerIds));
    }

    /**
     * Create data provider definition
     *
     * @param string           $name      name of the provider, e.g., sis or xml
     * @param array            $config    the configuration for the provider
     * @param ContainerBuilder $container DI container
     *
     * @return Reference the reference of the provider service
     */
    private function createDataProvider($name, $config, ContainerBuilder $container)
    {
        // a custom provider service, which is refered here by ID
        if (isset($config['id'])) {
            return new Reference($config['id']);
        }

        $providerId = null;
        if ('sis' == $name) {
            $providerId = 'ubc_lt_commons.provider.sis';
        } elseif ('xml' == $name) {
            $providerId = 'ubc_lt_commons.provider.xml';
        }

        $auth = null;
        if (isset($config['auth'])) {
            $auth = $this->createAuthModule($providerId, $config['auth'], $container);
        }

        $http_client = null;
        if (isset($config['http_client'])) {
            $http_client = $this->createHttpClient($providerId, $config['http_client'], $container);
        }

        $serializer = null;
        if (isset($config['serializer'])) {
            $serializer = $this->createSerializer($providerId, $config['serializer'], $container);
        }

        if ('sis' == $name) {
            $id = 'ubc_lt_commons.provider.sis';
            $container->register($id, '%ubc_lt_commons.data_provider.sis.class%')
                ->addArgument($config['base_url'])
                ->addArgument($http_client)
                ->addArgument($auth)
                ->addArgument($serializer);
        } elseif ('xml' == $name) {
            $id = 'ubc_lt_commons.provider.xml';
            $container->register($id, '%ubc_lt_commons.data_provider.xml.class%')
                ->addArgument($config['path'])
                ->addArgument($serializer);
        } else {
            throw new InvalidConfigurationException(sprintf('Unable to create definition for "%s" provider', $name));
        }

        return new Reference($id);
    }

    private function createAuthModule($providerId, $config, ContainerBuilder $container)
    {
        if (isset($config['id'])) {
            return new Reference($config['id']);
        }

        $id = $providerId . '.' . $config['module'];
        switch ($config['module']) {
            case 'Auth2':
                $container->register($providerId . '.xml_rpc_client', '%ubc_lt_commons.rpc_client.class%')
                    ->addArgument($config['rpc_path'])
                    ->addArgument($config['service_url'])
                    ->setPublic(false);
                $container->register($id, '%ubc_lt_commons.auth_module.auth2.class%')
                    ->addArgument(new Reference($providerId . '.xml_rpc_client'))
                    ->addArgument($config['username'])
                    ->addArgument($config['password'])
                    ->addArgument($config['service_application'])
                    ->addArgument($config['service_url'])
                    ->setPublic(false);
                break;
            case 'HttpBasic':
                $container->register($id, '%ubc_lt_commons.auth_module.httpbasic.class%')
                    ->addArgument($config['username'])
                    ->addArgument($config['password'])
                    ->setPublic(false);
                break;
            default:
                throw new InvalidConfigurationException(sprintf(
                    'Unable to create definition for "%s" authentication module', $config['module']
                ));
        }

        return new Reference($id);
    }

    private function createHttpClient($providerId, $config, ContainerBuilder $container)
    {
        $id = $providerId . '.' . $config;

        switch ($config) {
            case 'Guzzle':
                $container->register($id, '%ubc_lt_commons.http_client.guzzle.class%')
                    ->setPublic(false);
                break;
            default:
                throw new InvalidConfigurationException(sprintf(
                    'Unsupported Http Client "%s"', $config
                ));

        }

        return new Reference($id);
    }

    private function createSerializer($providerId, $config, ContainerBuilder $container)
    {
        $id = $providerId . '.' . $config;

        switch ($config) {
            case 'JMS':
                $container->register($id, '%ubc_lt_commons.serializer.jms.class%')
                    ->setPublic(false);
                break;
            default:
                throw new InvalidConfigurationException(sprintf(
                    'Unsupported serializer "%s"', $config
                ));

        }

        return new Reference($id);

    }
}
