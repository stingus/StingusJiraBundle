<?php

namespace Stingus\JiraBundle\Controller;

use GuzzleHttp\Exception\ClientException;
use Stingus\JiraBundle\Exception\ModelException;
use Stingus\JiraBundle\Model\OauthTokenInterface;
use Stingus\JiraBundle\Oauth\Oauth;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OauthController
 *
 * @package Stingus\JiraBundle\Controller
 */
class OauthController extends Controller
{
    /**
     * Connect to Jira endpoint
     *
     * @param Request $request
     * @param string  $consumerKey
     * @param string  $baseUrl
     *
     * @return RedirectResponse
     */
    public function connectAction(Request $request, string $consumerKey, string $baseUrl): RedirectResponse
    {
        try {
            $tokenClass = $this->getParameter('stingus_jira.oauth_token_class');
            /** @var OauthTokenInterface $oauthToken */
            $oauthToken = new $tokenClass();
            $oauthToken
                ->setConsumerKey($consumerKey)
                ->setBaseUrl($baseUrl);
            $redirectUrl = $this->get(Oauth::SERVICE_ID)->getRequestEndpoint($oauthToken);
        } catch (ModelException $exception) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'jira.errors.model',
                    ['%parameters%' => sprintf('(consumer key: %s, URL: %s)', $consumerKey, $baseUrl)],
                    'StingusJiraBundle'
                )
            );

            $redirectUrl = $request->headers->get('referer');
        } catch (ClientException $exception) {
            if (Response::HTTP_UNAUTHORIZED === $exception->getCode()) {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans(
                        'jira.errors.unauthorized',
                        ['%consumerKey%' => $consumerKey],
                        'StingusJiraBundle'
                    )
                );
            } else {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('jira.errors.general', [], 'StingusJiraBundle')
                );
            }

            $redirectUrl = $request->headers->get('referer');
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * Callback action, receiving OAuth token and verifier
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function callbackAction(Request $request): RedirectResponse
    {
        $oauthToken = null;
        if (null !== $tokenManager = $this->get('stingus_jira.oauth_token_manager')) {
            $oauthToken = $tokenManager->findByConsumerKey($request->query->get('consumer_key'));
        }

        if (null === $oauthToken) {
            $tokenClass = $this->getParameter('stingus_jira.oauth_token_class');
            /** @var OauthTokenInterface $oauthToken */
            $oauthToken = new $tokenClass();
        }

        try {
            $oauthToken
                ->setConsumerKey($request->query->get('consumer_key'))
                ->setBaseUrl($request->query->get('base_url'))
                ->setToken($request->query->get('oauth_token'))
                ->setVerifier($request->query->get('oauth_verifier'));
            $this->get(Oauth::SERVICE_ID)->getAccessToken($oauthToken);
        } catch (ModelException $exception) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'jira.errors.model',
                    ['%parameters%' => ''],
                    'StingusJiraBundle'
                )
            );
        } catch (ClientException $exception) {
            if (Response::HTTP_UNAUTHORIZED === $exception->getCode()) {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('jira.errors.denied', [], 'StingusJiraBundle')
                );
            } else {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('jira.errors.general', [], 'StingusJiraBundle')
                );
            }
        }

        return $this->redirect($this->getParameter('stingus_jira.redirect_url'));
    }
}
