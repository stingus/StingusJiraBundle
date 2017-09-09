<?php

namespace Stingus\JiraBundle\Controller;

use GuzzleHttp\Exception\ClientException;
use Stingus\JiraBundle\Model\OauthTokenInterface;
use Stingus\JiraBundle\Oauth\Oauth;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     *
     * @return RedirectResponse
     */
    public function connectAction(Request $request): RedirectResponse
    {
        $tokenId = $request->query->get('tokenId');
        $consumerKey = $request->query->get('consumerKey');
        $baseUrl = $request->query->get('baseUrl');
        if (null === $redirectUrl = $request->headers->get('referer')) {
            $redirectUrl = $this->getParameter('stingus_jira.redirect_url');
        }

        try {
            $tokenClass = $this->getParameter('stingus_jira.oauth_token_class');
            /** @var OauthTokenInterface $oauthToken */
            $oauthToken = new $tokenClass();
            $oauthToken
                ->setConsumerKey($consumerKey)
                ->setBaseUrl($baseUrl);
            if (null !== $tokenId) {
                $oauthToken->setId($tokenId);
            }
            $redirectUrl = $this->get(Oauth::SERVICE_ID)->getRequestEndpoint($oauthToken);
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
        try {
            $oauthToken = null;
            if ($this->has('stingus_jira.oauth_token_manager')) {
                $tokenManager = $this->get('stingus_jira.oauth_token_manager');
                if (null === $tokenId = $request->query->get('token_id')) {
                    throw new BadRequestHttpException('token_id query parameter is missing');
                }
                $oauthToken = $tokenManager->getRepository()->find($tokenId);
            }

            if (null === $oauthToken) {
                $tokenClass = $this->getParameter('stingus_jira.oauth_token_class');
                /** @var OauthTokenInterface $oauthToken */
                $oauthToken = new $tokenClass();
            }

            $oauthToken
                ->setConsumerKey($request->query->get('consumer_key'))
                ->setBaseUrl($request->query->get('base_url'))
                ->setToken($request->query->get('oauth_token'))
                ->setVerifier($request->query->get('oauth_verifier'));
            $this->get(Oauth::SERVICE_ID)->getAccessToken($oauthToken);
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
        } catch (BadRequestHttpException $exception) {
            $this->addFlash(
                'error',
                $this->get('translator')->trans(
                    'jira.errors.token_id_missing',
                    [],
                    'StingusJiraBundle'
                )
            );
        }

        return $this->redirect($this->getParameter('stingus_jira.redirect_url'));
    }
}
