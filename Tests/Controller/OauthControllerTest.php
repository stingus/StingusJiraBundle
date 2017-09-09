<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Controller;

use Doctrine\Common\Persistence\ObjectRepository;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Stingus\JiraBundle\Controller\OauthController;
use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\Doctrine\OauthTokenManager;
use Stingus\JiraBundle\Model\OauthTokenInterface;
use Stingus\JiraBundle\Oauth\Oauth;
use Stingus\JiraBundle\Tests\Fixtures\OauthToken;
use Stingus\JiraBundle\Tests\OauthTokenFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class OauthControllerTest extends TestCase
{
    /**
     * @dataProvider connectTokenIdProvider
     *
     * @param $tokenId
     */
    public function testConnectRedirect($tokenId)
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getRequestEndpoint')
            ->with(
                $this->callback(function (OauthTokenInterface $oauthToken) use ($tokenId) {
                    return $tokenId === $oauthToken->getId();
                })
            )
            ->willReturn('https://jira_request_endpoint');

        $container
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                ['stingus_jira.redirect_url'],
                ['stingus_jira.oauth_token_class']
            )
            ->willReturn('https://redirect_url', OauthToken::class);

        $container
            ->expects($this->exactly(1))
            ->method('get')
            ->with(Oauth::SERVICE_ID)
            ->willReturn($oauthMock);

        $controller = new OauthController();
        $controller->setContainer($container);

        $request = new Request([
            'tokenId' => $tokenId,
            'consumerKey' => 'consumer_key',
            'baseUrl' => 'https://example.com'
        ]);

        /** @noinspection PhpParamsInspection */
        $response = $controller->connectAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://jira_request_endpoint', $response->getTargetUrl());
    }

    public function testConnectRedirectNoReferrer()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(Request::class)->getMock();
        $queryMock = $this->getMockBuilder(ParameterBagInterface::class)->getMock();
        $headersMock = $this->getMockBuilder(ParameterBagInterface::class)->getMock();
        $flashBagMock = $this->getMockBuilder(FlashBagInterface::class)->getMock();
        $sessionMock = $this->getMockBuilder(Session::class)->getMock();
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();

        $clientException = new ClientException(
            'Exception message',
            $this->getMockBuilder(RequestInterface::class)->getMock()
        );

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getRequestEndpoint')
            ->with($this->isInstanceOf(OauthTokenInterface::class))
            ->willThrowException($clientException);

        $flashBagMock
            ->expects($this->exactly(1))
            ->method('add')
            ->with('error', 'Error message');

        $sessionMock
            ->expects($this->exactly(1))
            ->method('getFlashBag')
            ->willReturn($flashBagMock);

        $translatorMock
            ->expects($this->exactly(1))
            ->method('trans')
            ->willReturn('Error message');

        $queryMock
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                ['tokenId'],
                ['consumerKey'],
                ['baseUrl']
            )
            ->willReturn(null, '', '');

        $headersMock
            ->expects($this->exactly(1))
            ->method('get')
            ->with('referer')
            ->willReturn(null);

        $container
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [Oauth::SERVICE_ID],
                ['translator'],
                ['session']
            )
            ->willReturn($oauthMock, $translatorMock, $sessionMock);
        $container
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                ['stingus_jira.redirect_url'],
                ['stingus_jira.oauth_token_class']
            )
            ->willReturn('https://redirect_url', OauthToken::class);
        $container
            ->expects($this->exactly(1))
            ->method('has')
            ->with('session')
            ->willReturn(true);

        /** @noinspection PhpUndefinedFieldInspection */
        $requestMock->query = $queryMock;
        /** @noinspection PhpUndefinedFieldInspection */
        $requestMock->headers = $headersMock;

        $controller = new OauthController();
        $controller->setContainer($container);

        /** @noinspection PhpParamsInspection */
        $response = $controller->connectAction($requestMock);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://redirect_url', $response->getTargetUrl());
    }

    public function testConnectWithClientException()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();
        $flashBagMock = $this->getMockBuilder(FlashBagInterface::class)->getMock();
        $sessionMock = $this->getMockBuilder(Session::class)->getMock();
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(RequestInterface::class)->getMock();

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getRequestEndpoint')
            ->willThrowException(new ClientException('Exception message', $requestMock));

        $translatorMock
            ->expects($this->exactly(1))
            ->method('trans')
            ->with('jira.errors.general', [], 'StingusJiraBundle')
            ->willReturn('Error message');

        $flashBagMock
            ->expects($this->exactly(1))
            ->method('add')
            ->with('error', 'Error message');

        $sessionMock
            ->expects($this->exactly(1))
            ->method('getFlashBag')
            ->willReturn($flashBagMock);

        $container
            ->expects($this->exactly(1))
            ->method('getParameter')
            ->with('stingus_jira.oauth_token_class')
            ->willReturn(OauthToken::class);

        $container
            ->expects($this->exactly(1))
            ->method('has')
            ->with('session')
            ->willReturn(true);

        $container
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [Oauth::SERVICE_ID],
                ['translator'],
                ['session']
            )
            ->willReturn($oauthMock, $translatorMock, $sessionMock);

        $controller = new OauthController();
        $controller->setContainer($container);

        $request = new Request([
            'tokenId' => 1,
            'consumerKey' => 'consumer_key',
            'baseUrl' => 'https://example.com'],
            [], [], [], [], ['HTTP_REFERER' => 'https://example.com/referer']
        );

        /** @noinspection PhpParamsInspection */
        $response = $controller->connectAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://example.com/referer', $response->getTargetUrl());
    }

    public function testConnectWithClientUnauthorizedException()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();
        $flashBagMock = $this->getMockBuilder(FlashBagInterface::class)->getMock();
        $sessionMock = $this->getMockBuilder(Session::class)->getMock();
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(RequestInterface::class)->getMock();
        $responseMock = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $responseMock
            ->expects($this->exactly(1))
            ->method('getStatusCode')
            ->willReturn(401);

        $clientException = new ClientException('Exception message', $requestMock, $responseMock);

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getRequestEndpoint')
            ->willThrowException($clientException);

        $translatorMock
            ->expects($this->exactly(1))
            ->method('trans')
            ->with('jira.errors.unauthorized', ['%consumerKey%' => 'consumer_key'], 'StingusJiraBundle')
            ->willReturn('Error message');

        $flashBagMock
            ->expects($this->exactly(1))
            ->method('add')
            ->with('error', 'Error message');

        $sessionMock
            ->expects($this->exactly(1))
            ->method('getFlashBag')
            ->willReturn($flashBagMock);

        $container
            ->expects($this->exactly(1))
            ->method('getParameter')
            ->with('stingus_jira.oauth_token_class')
            ->willReturn(OauthToken::class);

        $container
            ->expects($this->exactly(1))
            ->method('has')
            ->with('session')
            ->willReturn(true);

        $container
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [Oauth::SERVICE_ID],
                ['translator'],
                ['session']
            )
            ->willReturn($oauthMock, $translatorMock, $sessionMock);

        $controller = new OauthController();
        $controller->setContainer($container);

        $request = new Request([
            'tokenId' => 1,
            'consumerKey' => 'consumer_key',
            'baseUrl' => 'https://example.com'],
            [], [], [], [], ['HTTP_REFERER' => 'https://example.com/referer']
        );

        /** @noinspection PhpParamsInspection */
        $response = $controller->connectAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://example.com/referer', $response->getTargetUrl());
    }

    public function testCallbackWithTokenManagerOauthTokenFound()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();
        $repositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();

        $oathToken = OauthTokenFactory::getBasicOauthToken();
        $oathToken
            ->setToken('oauthToken')
            ->setVerifier('oauthVerifier');

        $tokenManagerMock = $this
            ->getMockBuilder(OauthTokenManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock
            ->expects($this->exactly(1))
            ->method('find')
            ->with(1)
            ->willReturn($oathToken);

        $tokenManagerMock
            ->expects($this->exactly(1))
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $container
            ->expects($this->exactly(1))
            ->method('has')
            ->with('stingus_jira.oauth_token_manager')
            ->willReturn(true);

        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['stingus_jira.oauth_token_manager'],
                [Oauth::SERVICE_ID]
            )
            ->willReturn($tokenManagerMock, $oauthMock);

        $container
            ->expects($this->exactly(1))
            ->method('getParameter')
            ->with('stingus_jira.redirect_url')
            ->willReturn('https://redirect_url');

        $request = new Request([
            'token_id' => 1,
            'consumer_key' => 'consumerKey',
            'base_url' => 'https://example.com',
            'oauth_token' => 'oauthToken',
            'oauth_verifier' => 'oauthVerifier',
        ]);

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getAccessToken')
            ->with($oathToken);

        $controller = new OauthController();
        $controller->setContainer($container);

        $response = $controller->callbackAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCallbackWithTokenManagerOauthTokenNotFound()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();
        $repositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();
        $tokenManagerMock = $this
            ->getMockBuilder(OauthTokenManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $oathToken = OauthTokenFactory::getBasicOauthToken();
        $oathToken->setToken('oauthToken')->setVerifier('oauthVerifier');

        $repositoryMock
            ->expects($this->exactly(1))
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $tokenManagerMock
            ->expects($this->exactly(1))
            ->method('getRepository')
            ->willReturn($repositoryMock);

        $container
            ->expects($this->exactly(1))
            ->method('has')
            ->with('stingus_jira.oauth_token_manager')
            ->willReturn(true);

        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['stingus_jira.oauth_token_manager'],
                [Oauth::SERVICE_ID]
            )
            ->willReturn($tokenManagerMock, $oauthMock);

        $container
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                ['stingus_jira.oauth_token_class'],
                ['stingus_jira.redirect_url']
            )
            ->willReturn(OauthToken::class, 'https://redirect_url');

        $request = new Request([
            'token_id' => 1,
            'consumer_key' => 'consumerKey',
            'base_url' => 'https://example.com',
            'oauth_token' => 'oauthToken',
            'oauth_verifier' => 'oauthVerifier',
        ]);

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getAccessToken')
            ->with($oathToken);

        $controller = new OauthController();
        $controller->setContainer($container);

        $response = $controller->callbackAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCallbackWithoutTokenManager()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();

        $oathToken = OauthTokenFactory::getBasicOauthToken();
        $oathToken->setToken('oauthToken')->setVerifier('oauthVerifier');

        $container
            ->expects($this->exactly(1))
            ->method('has')
            ->with('stingus_jira.oauth_token_manager')
            ->willReturn(false);

        $container
            ->expects($this->exactly(1))
            ->method('get')
            ->with(Oauth::SERVICE_ID)
            ->willReturn($oauthMock);

        $container
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                ['stingus_jira.oauth_token_class'],
                ['stingus_jira.redirect_url']
            )
            ->willReturn(OauthToken::class, 'https://redirect_url');

        $request = new Request([
            'consumer_key' => 'consumerKey',
            'base_url' => 'https://example.com',
            'oauth_token' => 'oauthToken',
            'oauth_verifier' => 'oauthVerifier',
        ]);

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getAccessToken')
            ->with($oathToken);

        $controller = new OauthController();
        $controller->setContainer($container);

        $response = $controller->callbackAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCallbackWithClientException()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();
        $flashBagMock = $this->getMockBuilder(FlashBagInterface::class)->getMock();
        $sessionMock = $this->getMockBuilder(Session::class)->getMock();
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(RequestInterface::class)->getMock();

        $translatorMock
            ->expects($this->exactly(1))
            ->method('trans')
            ->with('jira.errors.general', [], 'StingusJiraBundle')
            ->willReturn('Error message');

        $flashBagMock
            ->expects($this->exactly(1))
            ->method('add')
            ->with('error', 'Error message');

        $sessionMock
            ->expects($this->exactly(1))
            ->method('getFlashBag')
            ->willReturn($flashBagMock);

        $container
            ->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(
                ['stingus_jira.oauth_token_manager'],
                ['session']
            )
            ->willReturn(false, true);

        $container
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [Oauth::SERVICE_ID],
                ['translator'],
                ['session']
            )
            ->willReturn($oauthMock, $translatorMock, $sessionMock);

        $container
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                ['stingus_jira.oauth_token_class'],
                ['stingus_jira.redirect_url']
            )
            ->willReturn(OauthToken::class, 'https://redirect_url');

        $request = new Request([
            'consumer_key' => 'consumerKey',
            'base_url' => 'https://example.com',
            'oauth_token' => 'oauthToken',
            'oauth_verifier' => 'oauthVerifier',
        ]);

        $oathToken = OauthTokenFactory::getBasicOauthToken();
        $oathToken->setToken('oauthToken')->setVerifier('oauthVerifier');

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getAccessToken')
            ->with($oathToken)
            ->willThrowException(new ClientException('Exception message', $requestMock));

        $controller = new OauthController();
        $controller->setContainer($container);

        /** @noinspection PhpParamsInspection */
        $response = $controller->callbackAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://redirect_url', $response->getTargetUrl());
    }

    public function testCallbackWithClientUnauthorizedException()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();
        $flashBagMock = $this->getMockBuilder(FlashBagInterface::class)->getMock();
        $sessionMock = $this->getMockBuilder(Session::class)->getMock();
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(RequestInterface::class)->getMock();
        $responseMock = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $responseMock
            ->expects($this->exactly(1))
            ->method('getStatusCode')
            ->willReturn(401);

        $translatorMock
            ->expects($this->exactly(1))
            ->method('trans')
            ->with('jira.errors.denied', [], 'StingusJiraBundle')
            ->willReturn('Error message');

        $flashBagMock
            ->expects($this->exactly(1))
            ->method('add')
            ->with('error', 'Error message');

        $sessionMock
            ->expects($this->exactly(1))
            ->method('getFlashBag')
            ->willReturn($flashBagMock);

        $container
            ->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(
                ['stingus_jira.oauth_token_manager'],
                ['session']
            )
            ->willReturn(false, true);

        $container
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                [Oauth::SERVICE_ID],
                ['translator'],
                ['session']
            )
            ->willReturn($oauthMock, $translatorMock, $sessionMock);

        $container
            ->expects($this->exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                ['stingus_jira.oauth_token_class'],
                ['stingus_jira.redirect_url']
            )
            ->willReturn(OauthToken::class, 'https://redirect_url');

        $request = new Request([
            'consumer_key' => 'consumerKey',
            'base_url' => 'https://example.com',
            'oauth_token' => 'oauthToken',
            'oauth_verifier' => 'oauthVerifier',
        ]);

        $oathToken = OauthTokenFactory::getBasicOauthToken();
        $oathToken->setToken('oauthToken')->setVerifier('oauthVerifier');

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getAccessToken')
            ->with($oathToken)
            ->willThrowException(new ClientException('Exception message', $requestMock, $responseMock));

        $controller = new OauthController();
        $controller->setContainer($container);

        /** @noinspection PhpParamsInspection */
        $response = $controller->callbackAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://redirect_url', $response->getTargetUrl());
    }

    public function testCallbackWithTokenManagerAndMissingTokenId()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $flashBagMock = $this->getMockBuilder(FlashBagInterface::class)->getMock();
        $sessionMock = $this->getMockBuilder(Session::class)->getMock();
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $tokenManagerMock = $this
            ->getMockBuilder(OauthTokenManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translatorMock
            ->expects($this->exactly(1))
            ->method('trans')
            ->with('jira.errors.token_id_missing', [], 'StingusJiraBundle')
            ->willReturn('Error message');

        $flashBagMock
            ->expects($this->exactly(1))
            ->method('add')
            ->with('error', 'Error message');

        $sessionMock
            ->expects($this->exactly(1))
            ->method('getFlashBag')
            ->willReturn($flashBagMock);

        $container
            ->expects($this->exactly(2))
            ->method('has')
            ->withConsecutive(
                ['stingus_jira.oauth_token_manager'],
                ['session']
            )
            ->willReturn(true, true);

        $container
            ->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(
                ['stingus_jira.oauth_token_manager'],
                ['translator'],
                ['session']
            )
            ->willReturn($tokenManagerMock, $translatorMock, $sessionMock);

        $container
            ->expects($this->exactly(1))
            ->method('getParameter')
            ->withConsecutive(
                ['stingus_jira.redirect_url']
            )
            ->willReturn('https://redirect_url');

        $request = new Request([
            'consumer_key' => 'consumerKey',
            'base_url' => 'https://example.com',
            'oauth_token' => 'oauthToken',
            'oauth_verifier' => 'oauthVerifier',
        ]);

        $controller = new OauthController();
        $controller->setContainer($container);

        /** @noinspection PhpParamsInspection */
        $response = $controller->callbackAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://redirect_url', $response->getTargetUrl());
    }

    public function connectTokenIdProvider()
    {
        return [
            [null],
            [1]
        ];
    }
}
