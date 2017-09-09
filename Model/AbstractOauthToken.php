<?php

namespace Stingus\JiraBundle\Model;

/**
 * Class OauthToken
 *
 * @package Stingus\JiraBundle\Model
 */
class AbstractOauthToken implements OauthTokenInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $consumerKey;

    /** @var string */
    protected $baseUrl;

    /** @var string */
    protected $verifier;

    /** @var string */
    protected $token;

    /** @var string */
    protected $tokenSecret;

    /** @var \DateTime */
    protected $expiresAt;

    /** @var \DateTime */
    protected $authExpiresAt;

    /** @var string */
    protected $sessionHandle;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id): OauthTokenInterface
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function setConsumerKey(string $consumerKey): AbstractOauthToken
    {
        $this->consumerKey = $consumerKey;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUrl(string $baseUrl): AbstractOauthToken
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVerifier()
    {
        return $this->verifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setVerifier(string $verifier): OauthTokenInterface
    {
        $this->verifier = $verifier;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(string $token): OauthTokenInterface
    {
        $this->token = $token;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function setTokenSecret(string $tokenSecret): OauthTokenInterface
    {
        $this->tokenSecret = $tokenSecret;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiresAt(\DateTime $expiresAt): OauthTokenInterface
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthExpiresAt()
    {
        return $this->authExpiresAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthExpiresAt(\DateTime $authExpiresAt): OauthTokenInterface
    {
        $this->authExpiresAt = $authExpiresAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionHandle()
    {
        return $this->sessionHandle;
    }

    /**
     * {@inheritdoc}
     */
    public function setSessionHandle(string $sessionHandle): OauthTokenInterface
    {
        $this->sessionHandle = $sessionHandle;

        return $this;
    }
}
