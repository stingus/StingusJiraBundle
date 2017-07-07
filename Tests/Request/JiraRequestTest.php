<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\Model\OauthTokenInterface;
use Stingus\JiraBundle\Oauth\OauthClient;
use Stingus\JiraBundle\Request\JiraRequest;
use Stingus\JiraBundle\Tests\OauthTokenFactory;

class JiraRequestTest extends TestCase
{
    public function testPost()
    {
        $oauthToken = OauthTokenFactory::getBasicOauthToken();

        $container = [];
        $response = $this->getJiraRequest($oauthToken, $container)->post($oauthToken, 'path', ['a' => 1, 'b' => 2]);

        /** @var Request $request */
        $request = $container[0]['request'];
        $uri = $request->getUri();

        $this->assertEquals('response content', $response->getBody()->getContents());
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('path', $uri->getPath());
        $this->assertEquals('a=1&b=2', $uri->getQuery());
    }

    public function testGet()
    {
        $oauthToken = OauthTokenFactory::getBasicOauthToken();

        $container = [];
        $response = $this->getJiraRequest($oauthToken, $container)->get($oauthToken, 'path', ['a' => 1, 'b' => 2]);

        /** @var Request $request */
        $request = $container[0]['request'];
        $uri = $request->getUri();

        $this->assertEquals('response content', $response->getBody()->getContents());
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('path', $uri->getPath());
        $this->assertEquals('a=1&b=2', $uri->getQuery());
    }

    public function getJiraRequest(OauthTokenInterface $oauthToken, array &$container)
    {
        $history = Middleware::history($container);
        $handlerMock = new MockHandler([
            new Response(200, [], 'response content'),
        ]);
        $handlerStack = HandlerStack::create($handlerMock);
        $handlerStack->push($history);
        $client = new Client(['handler' => $handlerStack]);

        $oauthClientMock = $this->getMockBuilder(OauthClient::class)->disableOriginalConstructor()->getMock();
        $oauthClientMock
            ->expects($this->exactly(1))
            ->method('getClient')
            ->with($oauthToken)
            ->willReturn($client);

        return new JiraRequest($oauthClientMock);
    }
}
