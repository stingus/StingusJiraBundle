<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Oauth;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\Doctrine\OauthTokenManager;
use Stingus\JiraBundle\Event\OauthTokenGeneratedEvent;
use Stingus\JiraBundle\Exception\JiraAuthorizationException;
use Stingus\JiraBundle\Model\OauthTokenInterface;
use Stingus\JiraBundle\Oauth\Oauth;
use Stingus\JiraBundle\Oauth\OauthClient;
use Stingus\JiraBundle\Request\JiraRequest;
use Stingus\JiraBundle\StingusJiraEvents;
use Stingus\JiraBundle\Tests\OauthTokenFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OauthTest extends TestCase
{
    public function testGetRequestEndpoint()
    {
        $oauthToken = OauthTokenFactory::getBasicOauthToken();

        $handlerMock = new MockHandler([
            new Response(200, [], 'oauth_token=request_oauth_token'),
        ]);

        $this->assertEquals(
            'https://example.com/plugins/servlet/oauth/authorize?oauth_token=request_oauth_token',
            $this->getOauthForRequestEndpoint($oauthToken, $handlerMock)->getRequestEndpoint($oauthToken)
        );
    }

    public function testGetRequestEndpointWithoutTokenOnResponse()
    {
        $this->expectException(JiraAuthorizationException::class);
        $this->expectExceptionMessage('Request authorization oauth_token key missing');

        $oauthToken = OauthTokenFactory::getBasicOauthToken();

        $handlerMock = new MockHandler([
            new Response(200),
        ]);

        $this->getOauthForRequestEndpoint($oauthToken, $handlerMock)->getRequestEndpoint($oauthToken);
    }

    public function testGetAccessToken()
    {
        $oauthToken = OauthTokenFactory::getBasicOauthToken();
        $oauthToken->setVerifier('verifier');

        $handlerMock = new MockHandler([
            new Response(200, [], 'oauth_expires_in=1&oauth_authorization_expires_in=1&oauth_token=response_oauth_token&oauth_token_secret=response_oauth_token_secret&oauth_session_handle=response_oauth_session_handle'),
        ]);

        $this->getOauthForAccessToken($oauthToken, $handlerMock)->getAccessToken($oauthToken);
    }

    public function testGetAccessTokenWithManager()
    {
        $oauthToken = OauthTokenFactory::getBasicOauthToken();
        $oauthToken->setVerifier('verifier');

        $handlerMock = new MockHandler([
            new Response(200, [], 'oauth_expires_in=1&oauth_authorization_expires_in=1&oauth_token=response_oauth_token&oauth_token_secret=response_oauth_token_secret&oauth_session_handle=response_oauth_session_handle'),
        ]);

        $tokenManager = $this->getMockBuilder(OauthTokenManager::class)->disableOriginalConstructor()->getMock();
        $tokenManager
            ->expects($this->exactly(1))
            ->method('save')
            ->with($oauthToken);

        $this->getOauthForAccessToken($oauthToken, $handlerMock, $tokenManager)->getAccessToken($oauthToken);
    }

    public function testGetAccessTokenWithoutVerifier()
    {
        $this->expectException(JiraAuthorizationException::class);
        $this->expectExceptionMessage('Verifier missing from the OauthToken');

        $oauthToken = OauthTokenFactory::getBasicOauthToken();

        $routerMock = $this->getMockBuilder(Router::class)->disableOriginalConstructor()->getMock();
        $oauthClientMock = $this->getMockBuilder(OauthClient::class)->disableOriginalConstructor()->getMock();
        $jiraRequest = new JiraRequest($oauthClientMock);
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        $oauth = new Oauth($routerMock, $jiraRequest, $dispatcher);
        $oauth->getAccessToken($oauthToken);
    }

    /**
     * @param $response
     * @param $exceptionMessage
     *
     * @dataProvider invalidAccessTokenResponseProvider
     */
    public function testGetAccessTokenInvalidResponse($response, $exceptionMessage)
    {
        $this->expectException(JiraAuthorizationException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $oauthToken = OauthTokenFactory::getBasicOauthToken();
        $oauthToken->setVerifier('verifier');

        $handlerMock = new MockHandler([
            new Response(200, [], $response),
        ]);

        $this->getOauthForInvalidAccessToken($oauthToken, $handlerMock)->getAccessToken($oauthToken);
    }

    public function invalidAccessTokenResponseProvider()
    {
        return [
            'Missing oauth_token' => [
                'oauth_token_secret=response_oauth_token_secret&oauth_expires_in=1&oauth_authorization_expires_in=1&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_token key',
            ],
            'Empty oauth_token' => [
                'oauth_token=&oauth_token_secret=response_oauth_token_secret&oauth_expires_in=1&oauth_authorization_expires_in=1&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_token key',
            ],
            'Missing oauth_token_secret' => [
                'oauth_token=response_oauth_token&oauth_expires_in=1&oauth_authorization_expires_in=1&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_token_secret key',
            ],
            'Empty oauth_token_secret' => [
                'oauth_token=response_oauth_token&oauth_token_secret=&oauth_expires_in=1&oauth_authorization_expires_in=1&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_token_secret key',
            ],
            'Missing oauth_expires_in' => [
                'oauth_token=response_oauth_token&oauth_token_secret=response_oauth_token_secret&oauth_authorization_expires_in=1&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_expires_in key',
            ],
            'Empty oauth_expires_in' => [
                'oauth_token=response_oauth_token&oauth_token_secret=response_oauth_token_secret&oauth_expires_in=&oauth_authorization_expires_in=1&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_expires_in key',
            ],
            'String oauth_expires_in' => [
                'oauth_token=response_oauth_token&oauth_token_secret=response_oauth_token_secret&oauth_expires_in=a&oauth_authorization_expires_in=1&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_expires_in key',
            ],
            'Missing oauth_authorization_expires_in' => [
                'oauth_token=response_oauth_token&oauth_token_secret=response_oauth_token_secret&oauth_expires_in=1&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_authorization_expires_in key',
            ],
            'Empty oauth_authorization_expires_in' => [
                'oauth_token=response_oauth_token&oauth_token_secret=response_oauth_token_secret&oauth_expires_in=1&oauth_authorization_expires_in=&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_authorization_expires_in key',
            ],
            'String oauth_authorization_expires_in' => [
                'oauth_token=response_oauth_token&oauth_token_secret=response_oauth_token_secret&oauth_expires_in=1&oauth_authorization_expires_in=a&oauth_session_handle=response_oauth_session_handle',
                'Invalid oauth_authorization_expires_in key',
            ],
        ];
    }

    private function getOauthForRequestEndpoint(OauthTokenInterface $oauthToken, MockHandler $clientHandler)
    {
        $routerMock = $this->getMockBuilder(Router::class)->disableOriginalConstructor()->getMock();
        $routerMock
            ->expects($this->exactly(1))
            ->method('generate')
            ->with(
                'stingus_jira_callback',
                [
                    'token_id' => null,
                    'consumer_key' => $oauthToken->getConsumerKey(),
                    'base_url' => $oauthToken->getBaseUrl()
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

        $handlerStack = HandlerStack::create($clientHandler);
        $client = new Client(['handler' => $handlerStack]);

        $oauthClientMock = $this->getMockBuilder(OauthClient::class)->disableOriginalConstructor()->getMock();
        $oauthClientMock
            ->expects($this->exactly(1))
            ->method('getClient')
            ->with($oauthToken)
            ->willReturn($client);

        $jiraRequest = new JiraRequest($oauthClientMock);
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        return new Oauth($routerMock, $jiraRequest, $dispatcher);
    }

    private function getOauthForAccessToken(OauthTokenInterface $oauthToken, MockHandler $handlerMock, OauthTokenManager $tokenManager = null)
    {
        $routerMock = $this->getMockBuilder(Router::class)->disableOriginalConstructor()->getMock();

        $handlerStack = HandlerStack::create($handlerMock);
        $client = new Client(['handler' => $handlerStack]);

        $oauthClientMock = $this->getMockBuilder(OauthClient::class)->disableOriginalConstructor()->getMock();
        $oauthClientMock
            ->expects($this->exactly(1))
            ->method('getClient')
            ->with($oauthToken)
            ->willReturn($client);

        $jiraRequest = new JiraRequest($oauthClientMock);
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $dispatcher
            ->expects($this->exactly(1))
            ->method('dispatch')
            ->with(
                StingusJiraEvents::OAUTH_TOKEN_GENERATE,
                $this->callback(function (OauthTokenGeneratedEvent $event) {
                    return $event->getOauthToken() instanceof OauthTokenInterface;
                }));

        return new Oauth($routerMock, $jiraRequest, $dispatcher, $tokenManager);
    }

    private function getOauthForInvalidAccessToken(OauthTokenInterface $oauthToken, MockHandler $handlerMock)
    {
        $routerMock = $this->getMockBuilder(Router::class)->disableOriginalConstructor()->getMock();

        $handlerStack = HandlerStack::create($handlerMock);
        $client = new Client(['handler' => $handlerStack]);

        $oauthClientMock = $this->getMockBuilder(OauthClient::class)->disableOriginalConstructor()->getMock();
        $oauthClientMock
            ->expects($this->exactly(1))
            ->method('getClient')
            ->with($oauthToken)
            ->willReturn($client);

        $jiraRequest = new JiraRequest($oauthClientMock);
        $dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $dispatcher
            ->expects($this->never())
            ->method('dispatch');

        return new Oauth($routerMock, $jiraRequest, $dispatcher);
    }
}
