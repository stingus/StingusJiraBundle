<?php

namespace Stingus\JiraBundle\Controller;

use Stingus\JiraBundle\Exception\ModelException;
use Stingus\JiraBundle\Oauth\Oauth;
use Stingus\JiraBundle\Model\OauthToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
            $oauthToken = new OauthToken($consumerKey, $baseUrl);
        } catch (ModelException $exception) {
            $this->addFlash('error', $this->get('translator')->trans('connect.jira.error', [], 'StingusJiraBundle'));

            return $this->redirect($request->headers->get('referer'));
        }

        return $this->redirect(
            $this->get(Oauth::SERVICE_ID)->getRequestEndpoint($oauthToken)
        );
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
            $oauthToken = new OauthToken($request->query->get('consumer_key'), $request->query->get('base_url'));
            $oauthToken
                ->setToken($request->query->get('oauth_token'))
                ->setVerifier($request->query->get('oauth_verifier'));
            $this->get(Oauth::SERVICE_ID)->getAccessToken($oauthToken);
        } catch (ModelException $exception) {
            $this->addFlash('error', $this->get('translator')->trans('connect.jira.error', [], 'StingusJiraBundle'));
        }

        return $this->redirect($this->getParameter('stingus_jira.redirect_url'));
    }
}
