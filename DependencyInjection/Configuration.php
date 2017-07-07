<?php

namespace Stingus\JiraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stingus_jira');

        $supportedDrivers = ['orm'];

        $rootNode
            ->children()
                ->arrayNode('mapping')
                    ->children()
                        ->scalarNode('driver')
                            ->validate()
                                ->ifNotInArray($supportedDrivers)
                                ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
                            ->end()
                        ->end()
                        ->scalarNode('model_manager_name')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('oauth_token_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cert_path')
                    ->defaultValue('var/certs')
                ->end()
                ->scalarNode('redirect_url')
                    ->isRequired()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
