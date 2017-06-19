<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Controller;

use Stingus\JiraBundle\Controller\OauthController;
use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\Model\OauthToken;
use Stingus\JiraBundle\Oauth\Oauth;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class OauthControllerTest extends TestCase
{
    public function testConnectRedirect()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getRequestEndpoint')
            ->willReturn('https://jira_request_endpoint');

        $container
            ->expects($this->exactly(1))
            ->method('get')
            ->with($this->equalTo(Oauth::SERVICE_ID))
            ->willReturn($oauthMock);

        $controller = new OauthController();
        $controller->setContainer($container);
        $requestMock = $this->getMockBuilder(Request::class)->getMock();

        /** @noinspection PhpParamsInspection */
        $response = $controller->connectAction($requestMock, 'consumer_key', 'https://example.com');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://jira_request_endpoint', $response->getTargetUrl());
    }

    public function testConnectWithModelException()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $flashBagMock = $this->getMockBuilder(FlashBagInterface::class)->getMock();
        $sessionMock = $this->getMockBuilder(Session::class)->getMock();
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $translatorMock
            ->expects($this->exactly(1))
            ->method('trans')
            ->with('connect.jira.error', [], 'StingusJiraBundle')
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
            ->method('has')
            ->with('session')
            ->willReturn(true);

        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['translator'],
                ['session']
            )
            ->willReturn($translatorMock, $sessionMock);

        $controller = new OauthController();
        $controller->setContainer($container);

        $request = new Request([], [], [], [], [], ['HTTP_REFERER' => 'https://example.com/referer']);

        /** @noinspection PhpParamsInspection */
        $response = $controller->connectAction($request, '', 'https://example.com');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://example.com/referer', $response->getTargetUrl());
    }

    public function testCallbackRedirect()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $oauthMock = $this->getMockBuilder(Oauth::class)->disableOriginalConstructor()->getMock();

        $container
            ->expects($this->exactly(1))
            ->method('get')
            ->with($this->equalTo(Oauth::SERVICE_ID))
            ->willReturn($oauthMock);

        $container
            ->expects($this->exactly(1))
            ->method('getParameter')
            ->with($this->equalTo('stingus_jira.redirect_url'))
            ->willReturn('https://redirect_url');


        $request = new Request([
            'consumer_key' => 'consumerKey',
            'base_url' => 'https://example.com',
            'oauth_token' => 'oauthToken',
            'oauth_verifier' => 'oauthVerifier',
        ]);

        $oathToken = new OauthToken('consumerKey', 'https://example.com');
        $oathToken->setToken('oauthToken')->setVerifier('oauthVerifier');

        $oauthMock
            ->expects($this->exactly(1))
            ->method('getAccessToken')
            ->with($this->equalTo($oathToken));

        $controller = new OauthController();
        $controller->setContainer($container);

        $response = $controller->callbackAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCallbackWithModelException()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $flashBagMock = $this->getMockBuilder(FlashBagInterface::class)->getMock();
        $sessionMock = $this->getMockBuilder(Session::class)->getMock();
        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)->getMock();

        $translatorMock
            ->expects($this->exactly(1))
            ->method('trans')
            ->with('connect.jira.error', [], 'StingusJiraBundle')
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
            ->method('has')
            ->with('session')
            ->willReturn(true);

        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['translator'],
                ['session']
            )
            ->willReturn($translatorMock, $sessionMock);

        $container
            ->expects($this->exactly(1))
            ->method('getParameter')
            ->with($this->equalTo('stingus_jira.redirect_url'))
            ->willReturn('https://redirect_url');

        $controller = new OauthController();
        $controller->setContainer($container);

        $request = new Request([
            'consumer_key' => '',
            'base_url' => '',
        ]);

        /** @noinspection PhpParamsInspection */
        $response = $controller->callbackAction($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('https://redirect_url', $response->getTargetUrl());
    }
}
