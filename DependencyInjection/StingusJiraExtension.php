<?php

namespace Stingus\JiraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class StingusJiraExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (array_key_exists('mapping', $config)) {
            $mappingConfig = $config['mapping'];
            if (array_key_exists('driver', $mappingConfig)) {
                $driverConfig = $mappingConfig['driver'];
                $loader->load(sprintf('%s.yml', $driverConfig));
                $container->setParameter('stingus_jira.backend_type_'.$driverConfig, true);
            }
            $container->setParameter('stingus_jira.model_manager_name', $mappingConfig['model_manager_name']);
        }

        $container->setParameter('stingus_jira.oauth_token_class', $config['oauth_token_class']);
        $container->setParameter('stingus_jira.cert_path', $config['cert_path']);
        $container->setParameter('stingus_jira.redirect_url', $config['redirect_url']);
        $container->setParameter('stingus_jira.timeout', $config['timeout']);
    }
}
