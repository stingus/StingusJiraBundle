<?php

namespace Stingus\JiraBundle\Oauth;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Stingus\JiraBundle\Model\OauthTokenInterface;

/**
 * Class OauthClient.
 * OAuth middleware for Guzzle 6
 *
 * @package Stingus\JiraBundle\Oauth
 */
class OauthClient
{
    const SERVICE_ID = 'stingus_jira.oauth_client';

    /** @var string */
    private $projectRoot;

    /** @var string Certificates path */
    private $certPath;

    /**
     * OauthClient constructor.
     *
     * @param string $projectRoot
     * @param string $certPath
     */
    public function __construct(string $projectRoot, string $certPath)
    {
        $this->projectRoot = $projectRoot;
        $this->certPath = $certPath;
    }

    /**
     * Get the HTTP Guzzle client
     *
     * @param OauthTokenInterface $oauthToken
     *
     * @return Client
     */
    public function getClient(OauthTokenInterface $oauthToken): Client
    {
        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key'           => $oauthToken->getConsumerKey(),
            'token'                  => $oauthToken->getToken(),
            'token_secret'           => $oauthToken->getTokenSecret(),
            'private_key_file'       => $this->projectRoot.DIRECTORY_SEPARATOR.$this->certPath.DIRECTORY_SEPARATOR.'private.key',
            'private_key_passphrase' => null,
            'signature_method'       => Oauth1::SIGNATURE_METHOD_RSA,
        ]);
        $stack->push($middleware);

        return new Client(
            [
                'base_uri' => $oauthToken->getBaseUrl(),
                'handler'  => $stack,
                'auth'     => 'oauth',
            ]
        );
    }
}
