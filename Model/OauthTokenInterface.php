<?php

namespace Stingus\JiraBundle\Model;

/**
 * Interface OauthTokenInterface
 *
 * @package Stingus\JiraBundle\Model
 */
interface OauthTokenInterface
{
    /**
     * Get the token internal id
     *
     * @return mixed
     */
    public function getId();

    /**
     * @param int|string $id
     *
     * @return OauthTokenInterface
     */
    public function setId($id): OauthTokenInterface;

    /**
     * Get the consumer key
     *
     * @return string
     */
    public function getConsumerKey();

    /**
     * @param string $consumerKey
     *
     * @return AbstractOauthToken
     */
    public function setConsumerKey(string $consumerKey): AbstractOauthToken;

    /**
     * Get JIRA base URL
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * @param string $baseUrl
     *
     * @return AbstractOauthToken
     */
    public function setBaseUrl(string $baseUrl): AbstractOauthToken;

    /**
     * Get the OAuth verifier
     *
     * @return string
     */
    public function getVerifier();

    /**
     * @param string $verifier
     *
     * @return OauthTokenInterface
     */
    public function setVerifier(string $verifier): OauthTokenInterface;

    /**
     * Get the OAuth token
     *
     * @return string
     */
    public function getToken();

    /**
     * @param string $token
     *
     * @return OauthTokenInterface
     */
    public function setToken(string $token): OauthTokenInterface;

    /**
     * Get the OAuth token secret
     *
     * @return string
     */
    public function getTokenSecret();

    /**
     * @param string $tokenSecret
     *
     * @return OauthTokenInterface
     */
    public function setTokenSecret(string $tokenSecret): OauthTokenInterface;

    /**
     * Get token expiration date
     *
     * @return \DateTime
     */
    public function getExpiresAt();

    /**
     * @param \DateTime $expiresAt
     *
     * @return OauthTokenInterface
     */
    public function setExpiresAt(\DateTime $expiresAt): OauthTokenInterface;

    /**
     * Get the authorization expiration date
     *
     * @return \DateTime
     */
    public function getAuthExpiresAt();

    /**
     * @param \DateTime $authExpiresAt
     *
     * @return OauthTokenInterface
     */
    public function setAuthExpiresAt(\DateTime$authExpiresAt): OauthTokenInterface;

    /**
     * Get the session handle
     *
     * @return string
     */
    public function getSessionHandle();

    /**
     * @param string $sessionHandle
     *
     * @return OauthTokenInterface
     */
    public function setSessionHandle(string $sessionHandle): OauthTokenInterface;
}
