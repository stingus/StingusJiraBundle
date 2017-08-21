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
     * @param string              $body
     *
     * @return ResponseInterface
     */
    public function post(
        OauthTokenInterface $oauthToken,
        string $url,
        ?array $query = null,
        ?string $body = null
    ): ResponseInterface {
        return $this->oauthClient
            ->getClient($oauthToken)
            ->post(
                $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'query' => $query,
                    'body' => $body,
                ]
            );
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
    public function get(OauthTokenInterface $oauthToken, string $url, ?array $query = null): ResponseInterface
    {
        return $this->oauthClient
            ->getClient($oauthToken)
            ->get($url, ['query' => $query]);
    }

    /**
     * Make a PUT request to Jira endpoint
     *
     * @param OauthTokenInterface $oauthToken
     * @param string              $url
     * @param array               $query
     * @param string              $body
     *
     * @return ResponseInterface
     */
    public function put(
        OauthTokenInterface $oauthToken,
        string $url,
        ?array $query = null,
        ?string $body = null
    ): ResponseInterface {
        return $this->oauthClient
            ->getClient($oauthToken)
            ->put(
                $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'query' => $query,
                    'body' => $body,
                ]
            );
    }

    /**
     * Make a DELETE request to Jira endpoint
     *
     * @param OauthTokenInterface $oauthToken
     * @param string              $url
     * @param array               $query
     *
     * @return ResponseInterface
     */
    public function delete(OauthTokenInterface $oauthToken, string $url, ?array $query = null): ResponseInterface
    {
        return $this->oauthClient
            ->getClient($oauthToken)
            ->delete($url, ['query' => $query]);
    }
}
