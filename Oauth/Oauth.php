<?php

namespace Stingus\JiraBundle\Oauth;

use Stingus\JiraBundle\Doctrine\OauthTokenManager;
use Stingus\JiraBundle\Event\OauthTokenGeneratedEvent;
use Stingus\JiraBundle\Exception\JiraAuthorizationException;
use Stingus\JiraBundle\Model\OauthTokenInterface;
use Stingus\JiraBundle\Request\JiraRequest;
use Stingus\JiraBundle\StingusJiraEvents;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class Oauth.
 * Responsible with Jira Oauth 1.0a authorization
 *
 * @package Stingus\JiraBundle\Oauth
 */
class Oauth
{
    const SERVICE_ID = 'stingus_jira.oauth';

    const FILENAME_PRIVATE = 'private.key';
    const FILENAME_PUBLIC  = 'public.key';
    const FILENAME_CERT    = 'cert.pem';

    const URL_REQUEST_TOKEN   = '/plugins/servlet/oauth/request-token';
    const URL_AUTHORIZE_TOKEN = '/plugins/servlet/oauth/authorize';
    const URL_ACCESS_TOKEN    = '/plugins/servlet/oauth/access-token';

    /** @var Router */
    private $router;

    /** @var JiraRequest */
    private $jiraRequest;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var OauthTokenManager */
    private $tokenManager;

    /**
     * Oauth constructor.
     *
     * @param Router                   $router
     * @param JiraRequest              $jiraRequest
     * @param EventDispatcherInterface $dispatcher
     * @param OauthTokenManager        $tokenManager
     */
    public function __construct(
        Router $router,
        JiraRequest $jiraRequest,
        EventDispatcherInterface $dispatcher,
        OauthTokenManager $tokenManager = null
    ) {
        $this->router = $router;
        $this->jiraRequest = $jiraRequest;
        $this->dispatcher = $dispatcher;
        $this->tokenManager = $tokenManager;
    }

    /**
     * Get the Oauth request endpoint, with the temporary token
     *
     * @param OauthTokenInterface $oauthToken
     *
     * @return string
     * @throws JiraAuthorizationException
     */
    public function getRequestEndpoint(OauthTokenInterface $oauthToken): string
    {
        $callback = $this->router
            ->generate(
                'stingus_jira_callback',
                [
                    'consumer_key' => $oauthToken->getConsumerKey(),
                    'base_url' => $oauthToken->getBaseUrl(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        $response = $this->jiraRequest
            ->post($oauthToken, self::URL_REQUEST_TOKEN, ['oauth_callback' => $callback])
            ->getBody()
            ->getContents();

        parse_str($response, $responseTokens);

        if (!array_key_exists('oauth_token', $responseTokens)) {
            throw new JiraAuthorizationException('Request authorization oauth_token key missing');
        }

        return sprintf(
            '%s%s?oauth_token=%s',
            $oauthToken->getBaseUrl(),
            self::URL_AUTHORIZE_TOKEN,
            $responseTokens['oauth_token']
        );
    }

    /**
     * Get the OAuth tokens (token and secret).
     *
     * @param OauthTokenInterface $oauthToken
     *
     * @throws JiraAuthorizationException
     */
    public function getAccessToken(OauthTokenInterface $oauthToken)
    {
        if (null === $oauthToken->getVerifier()) {
            throw new JiraAuthorizationException('Verifier missing from the OauthToken');
        }

        $response = $this->jiraRequest
            ->post($oauthToken, self::URL_ACCESS_TOKEN, ['oauth_verifier' => $oauthToken->getVerifier()])
            ->getBody()
            ->getContents();

        parse_str($response, $responseTokens);

        $this->validateAccessTokens($responseTokens);

        $expiresAt = (new \DateTime())
            ->add(new \DateInterval(sprintf('PT%sS', $responseTokens['oauth_expires_in'])));
        $authExpiresAt = (new \DateTime())
            ->add(new \DateInterval(sprintf('PT%sS', $responseTokens['oauth_authorization_expires_in'])));

        $oauthToken
            ->setToken($responseTokens['oauth_token'])
            ->setTokenSecret($responseTokens['oauth_token_secret'])
            ->setExpiresAt($expiresAt)
            ->setAuthExpiresAt($authExpiresAt)
            ->setSessionHandle($responseTokens['oauth_session_handle']);

        $this->dispatcher->dispatch(StingusJiraEvents::OAUTH_TOKEN_GENERATE, new OauthTokenGeneratedEvent($oauthToken));

        if (null !== $this->tokenManager) {
            $this->tokenManager->save($oauthToken);
        }
    }

    /**
     * Validate access tokens
     *
     * @param array $tokens
     */
    private function validateAccessTokens(array $tokens)
    {
        $this->validateAccessTokenKeyString('oauth_token', $tokens);
        $this->validateAccessTokenKeyString('oauth_token_secret', $tokens);
        $this->validateAccessTokenKeyInt('oauth_expires_in', $tokens);
        $this->validateAccessTokenKeyInt('oauth_authorization_expires_in', $tokens);
        $this->validateAccessTokenKeyString('oauth_session_handle', $tokens);
    }

    /**
     * Validate string access tokens
     *
     * @param string $key
     * @param array  $tokens
     *
     * @return bool
     * @throws JiraAuthorizationException
     */
    private function validateAccessTokenKeyString(string $key, array $tokens)
    {
        if (!array_key_exists($key, $tokens) || '' === $tokens[$key]) {
            throw new JiraAuthorizationException(sprintf('Invalid %s key', $key));
        }

        return true;
    }

    /**
     * Validate integer access tokens
     *
     * @param string $key
     * @param array  $tokens
     *
     * @return bool
     * @throws JiraAuthorizationException
     */
    private function validateAccessTokenKeyInt(string $key, array $tokens)
    {
        if (!array_key_exists($key, $tokens)
            || false === filter_var($tokens[$key], FILTER_VALIDATE_INT)
        ) {
            throw new JiraAuthorizationException(sprintf('Invalid %s key', $key));
        }

        return true;
    }
}
