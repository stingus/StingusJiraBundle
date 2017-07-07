<?php

namespace Stingus\JiraBundle\Model;

use Stingus\JiraBundle\Exception\ModelException;

/**
 * Class OauthToken
 *
 * @package Stingus\JiraBundle\Model
 */
class AbstractOauthToken implements OauthTokenInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $consumerKey;

    /** @var string */
    private $baseUrl;

    /** @var string */
    private $verifier;

    /** @var string */
    private $token;

    /** @var string */
    private $tokenSecret;

    /** @var \DateTime */
    private $expiresAt;

    /** @var \DateTime */
    private $authExpiresAt;

    /** @var string */
    private $sessionHandle;

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
        if (!is_int($id) && !is_string($id)) {
            throw new ModelException('The ID must be a string or a positive integer');
        }

        if (true === is_int($id) && $id <= 0) {
            throw new ModelException('An integer ID must be greater than 0');
        }

        if (true === is_string($id) && '' === $id) {
            throw new ModelException('A string ID must not be empty');
        }

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
        $keyLength = strlen($consumerKey);
        if (0 === $keyLength || $keyLength > 255) {
            throw new ModelException('Consumer key length must be between 0 and 255 characters');
        }

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
        if (false === filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new ModelException('Base URL is invalid');
        }

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
        if ('' === $verifier) {
            throw new ModelException('Verifier must not be empty');
        }

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
        if ('' === $token) {
            throw new ModelException('Token must not be empty');
        }

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
        if ('' === $tokenSecret) {
            throw new ModelException('Token secret must not be empty');
        }

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
        if ($expiresAt <= new \DateTime()) {
            throw new ModelException('Expire date must be in the future');
        }

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
        if ($authExpiresAt <= new \DateTime()) {
            throw new ModelException('Authorization expire date must be in the future');
        }

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
        if ('' === $sessionHandle) {
            throw new ModelException('Session handle must not be empty');
        }

        $this->sessionHandle = $sessionHandle;

        return $this;
    }
}
