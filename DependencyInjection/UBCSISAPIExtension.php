<?php

namespace UBC\SISAPIBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class UBCSISAPIExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // override the parameters in services.yml
        if (array_key_exists('Auth2', $config)) {
            $container->setParameter('sisapi.config.username', $config['Auth2']['username']);
            $container->setParameter('sisapi.config.password', $config['Auth2']['password']);
            $container->setParameter('sisapi.config.service_application', $config['Auth2']['service_application']);
            $container->setParameter('sisapi.config.service_url', $config['Auth2']['service_url']);
        }

        if (array_key_exists('SIS', $config)) {
            $container->setParameter('sisapi.config.base_url', $config['SIS']['base_url']);
        }
    }

    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     *
     * @api
     */
    public function getAlias()
    {
        return 'ubcsisapi';
    }
}
