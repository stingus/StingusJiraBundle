<?php

namespace Stingus\JiraBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Stingus\JiraBundle\Model\OauthToken;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class StingusJiraBundle
 *
 * @package Stingus\JiraBundle
 */
class StingusJiraBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $modelDir = realpath(__DIR__.'/Resources/config/doctrine-mapping');
        $mappings = [
            $modelDir => 'Stingus\JiraBundle\Model',
        ];

        if (class_exists(DoctrineOrmMappingsPass::class)) {
            $container->addCompilerPass(
                DoctrineOrmMappingsPass::createXmlMappingDriver(
                    $mappings,
                    ['stingus_jira.model_manager_name'],
                    'stingus_jira.backend_type_orm',
                    ['StingusJiraBundle' => OauthToken::class]
                )
            );
        }
    }
}
