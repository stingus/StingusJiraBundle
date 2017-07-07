<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\DependencyInjection\StingusJiraExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class StingusJiraExtensionTest extends TestCase
{
    public function testFullConfig()
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new StingusJiraExtension();
        $config = $this->getFullConfig();
        $loader->load(array($config), $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('stingus_jira.oauth_token_manager'));
        $this->assertTrue($containerBuilder->hasDefinition('stingus_jira.object_manager'));
        $this->assertTrue($containerBuilder->getParameter('stingus_jira.backend_type_orm'));
        $this->assertSame('custom_manager_name', $containerBuilder->getParameter('stingus_jira.model_manager_name'));
        $this->assertSame('custom_token_class', $containerBuilder->getParameter('stingus_jira.oauth_token_class'));
        $this->assertSame('path/to/certs', $containerBuilder->getParameter('stingus_jira.cert_path'));
        $this->assertSame('http://redirect_url', $containerBuilder->getParameter('stingus_jira.redirect_url'));
    }

    public function testWithoutMapping()
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new StingusJiraExtension();
        $config = $this->getFullConfig();
        unset($config['mapping']);
        $loader->load(array($config), $containerBuilder);

        $this->assertFalse($containerBuilder->hasDefinition('stingus_jira.oauth_token_manager'));
        $this->assertFalse($containerBuilder->hasDefinition('stingus_jira.object_manager'));
        $this->assertFalse($containerBuilder->hasParameter('stingus_jira.backend_type_orm'));
        $this->assertFalse($containerBuilder->hasParameter('stingus_jira.model_manager_name'));
    }

    public function testWithoutMappingDriver()
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new StingusJiraExtension();
        $config = $this->getFullConfig();
        unset($config['mapping']['driver']);
        $loader->load(array($config), $containerBuilder);

        $this->assertFalse($containerBuilder->hasDefinition('stingus_jira.oauth_token_manager'));
        $this->assertFalse($containerBuilder->hasDefinition('stingus_jira.object_manager'));
        $this->assertFalse($containerBuilder->hasParameter('stingus_jira.backend_type_orm'));
        $this->assertSame('custom_manager_name', $containerBuilder->getParameter('stingus_jira.model_manager_name'));
    }

    public function testWithInvalidMappingDriver()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageRegExp('/^Invalid configuration for path "stingus_jira\.mapping\.driver": The driver "invalid" is not supported/');

        $containerBuilder = new ContainerBuilder();
        $loader = new StingusJiraExtension();
        $config = $this->getFullConfig();
        $config['mapping']['driver'] = 'invalid';
        $loader->load(array($config), $containerBuilder);
    }

    public function testDefaultManager()
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new StingusJiraExtension();
        $config = $this->getFullConfig();
        unset($config['mapping']['model_manager_name']);
        $loader->load(array($config), $containerBuilder);

        $this->assertNull($containerBuilder->getParameter('stingus_jira.model_manager_name'));
    }

    public function testMissingOauthTokenClass()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The child node "oauth_token_class" at path "stingus_jira" must be configured.');

        $containerBuilder = new ContainerBuilder();
        $loader = new StingusJiraExtension();
        $config = $this->getFullConfig();
        unset($config['oauth_token_class']);
        $loader->load(array($config), $containerBuilder);
    }

    public function testMissingCertPath()
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new StingusJiraExtension();
        $config = $this->getFullConfig();
        unset($config['cert_path']);
        $loader->load(array($config), $containerBuilder);

        $this->assertSame('var/certs', $containerBuilder->getParameter('stingus_jira.cert_path'));
    }

    public function testMissingRedirectUrl()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageRegExp('/^The child node "redirect_url" at path "stingus_jira" must be configured\.$/');

        $containerBuilder = new ContainerBuilder();
        $loader = new StingusJiraExtension();
        $config = $this->getFullConfig();
        unset($config['redirect_url']);
        $loader->load(array($config), $containerBuilder);
    }

    private function getFullConfig()
    {
        $yaml = <<<EOF
mapping:
  driver: orm
  model_manager_name: custom_manager_name
oauth_token_class: custom_token_class
cert_path: path/to/certs
redirect_url: http://redirect_url
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }
}
