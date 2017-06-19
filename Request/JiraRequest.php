<?php

namespace Stingus\JiraBundle\Request;

use Psr\Http\Message\ResponseInterface;
use Stingus\JiraBundle\Oauth\OauthClient;
use Stingus\JiraBundle\Model\OauthTokenInterface;

/**
 * Class JiraRequest.
 * Make requests to Jira endpoints
 *
 * @package Stingus\JiraBundle\Request
 */
class JiraRequest
{
    const SERVICE_ID = 'stingus_jira.jira_request';

    /** @var OauthClient */
    private $oauthClient;

    /**
     * JiraRequest constructor.
     *
     * @param OauthClient $oauthClient
     */
    public function __construct(OauthClient $oauthClient)
    {
        $this->oauthClient = $oauthClient;
    }

    /**
     * Make a POST request to Jira endpoint
     *
     * @param OauthTokenInterface $oauthToken
     * @param string              $url
     * @param array               $query
     *
     * @return ResponseInterface
     */
    public function post(OauthTokenInterface $oauthToken, string $url, array $query = []): ResponseInterface
    {
        return $this->oauthClient
            ->getClient($oauthToken)
            ->post($url, ['query' => $query]);
    }

    /**
     * Make a GET request to Jira endpoint
     *
     * @param OauthTokenInterface $oauthToken
     * @param string              $url
     * @param array               $query
     *
     * @return ResponseInterface
     */
    public function get(OauthTokenInterface $oauthToken, string $url, array $query = []): ResponseInterface
    {
        return $this->oauthClient
            ->getClient($oauthToken)
            ->get($url, ['query' => $query]);
    }
}
