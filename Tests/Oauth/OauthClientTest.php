<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Oauth;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\Oauth\OauthClient;
use Stingus\JiraBundle\Tests\Fixtures\OauthToken;

class OauthClientTest extends TestCase
{
    public function testGetClient()
    {
        $oauthClient = new OauthClient('/project/root', 'cert/path', 10);
        $oauthToken = new OauthToken();
        $oauthToken
            ->setConsumerKey('consumer_key')
            ->setBaseUrl('https://example.com:80/jira_path?a=1&b=2')
            ->setToken('token')
            ->setTokenSecret('token_secret');
        $client = $oauthClient->getClient($oauthToken);
        $clientConfig = $client->getConfig();

        /** @var Uri $uri */
        $uri = $clientConfig['base_uri'];

        $this->assertEquals('example.com', $uri->getHost());
        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals(80, $uri->getPort());
        $this->assertEquals('/jira_path', $uri->getPath());
        $this->assertEquals('a=1&b=2', $uri->getQuery());
        $this->assertEquals('oauth', $clientConfig['auth']);
        $this->assertEquals(10, $clientConfig['timeout']);
    }
}
