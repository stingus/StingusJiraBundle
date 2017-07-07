<?php

namespace Stingus\JiraBundle\Tests;

use Stingus\JiraBundle\Tests\Fixtures\OauthToken;

class OauthTokenFactory
{
    public static function getBasicOauthToken()
    {
        $oauthToken = new OauthToken();
        $oauthToken
            ->setConsumerKey('consumerKey')
            ->setBaseUrl('https://example.com');

        return $oauthToken;
    }
}
