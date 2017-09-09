<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\Model\OauthTokenInterface;
use Stingus\JiraBundle\Tests\Fixtures\OauthToken;

class OauthTokenTest extends TestCase
{
    public function testInstantiate()
    {
        $this->assertInstanceOf(OauthTokenInterface::class, new OauthToken());
    }

    /**
     * @param mixed $consumerKey
     *
     * @dataProvider nonStringTypeProvider
     */
    public function testInvalidConsumerKeyType($consumerKey)
    {
        $this->expectException(\TypeError::class);

        $oauthToken = new OauthToken();
        $oauthToken->setConsumerKey($consumerKey);
    }

    /**
     * @param mixed $baseUrl
     *
     * @dataProvider nonStringTypeProvider
     */
    public function testInvalidBaseUrlType($baseUrl)
    {
        $this->expectException(\TypeError::class);

        $oauthToken = new OauthToken();
        $oauthToken->setBaseUrl($baseUrl);
    }

    /**
     * @param string|int $id
     *
     * @dataProvider validIdProvider
     */
    public function testValidId($id)
    {
        $oauthToken = new OauthToken();
        $oauthToken->setId($id);

        $this->assertEquals($id, $oauthToken->getId());
    }

    /**
     * @param string $verifier
     *
     * @dataProvider stringProvider
     */
    public function testValidVerifier($verifier)
    {
        $oauthToken = new OauthToken();
        $oauthToken->setVerifier($verifier);

        $this->assertEquals($verifier, $oauthToken->getVerifier());
    }

    /**
     * @param mixed $verifier
     *
     * @dataProvider nonStringTypeProvider
     */
    public function testInvalidTypeVerifier($verifier)
    {
        $this->expectException(\TypeError::class);

        $oauthToken = new OauthToken();
        $oauthToken->setVerifier($verifier);
    }

    /**
     * @param string $token
     *
     * @dataProvider stringProvider
     */
    public function testValidToken($token)
    {
        $oauthToken = new OauthToken();
        $oauthToken->setToken($token);

        $this->assertEquals($token, $oauthToken->getToken());
    }

    /**
     * @param mixed $token
     *
     * @dataProvider nonStringTypeProvider
     */
    public function testInvalidTypeToken($token)
    {
        $this->expectException(\TypeError::class);

        $oauthToken = new OauthToken();
        $oauthToken->setToken($token);
    }

    /**
     * @param string $tokenSecret
     *
     * @dataProvider stringProvider
     */
    public function testValidTokenSecret($tokenSecret)
    {
        $oauthToken = new OauthToken();
        $oauthToken->setTokenSecret($tokenSecret);

        $this->assertEquals($tokenSecret, $oauthToken->getTokenSecret());
    }

    /**
     * @param mixed $tokenSecret
     *
     * @dataProvider nonStringTypeProvider
     */
    public function testInvalidTypeTokenSecret($tokenSecret)
    {
        $this->expectException(\TypeError::class);

        $oauthToken = new OauthToken();
        $oauthToken->setTokenSecret($tokenSecret);
    }

    public function testValidExpiresAt()
    {
        $expiresAt = (new \DateTime())->add(new \DateInterval('PT1S'));
        $oauthToken = new OauthToken();
        $oauthToken->setExpiresAt($expiresAt);

        $this->assertEquals($expiresAt, $oauthToken->getExpiresAt());
    }

    /**
     * @param mixed $expiresAt
     *
     * @dataProvider nonDateTimeProvider
     */
    public function testInvalidTypeExpiresAt($expiresAt)
    {
        $this->expectException(\TypeError::class);

        $oauthToken = new OauthToken();
        $oauthToken->setExpiresAt($expiresAt);
    }

    public function testValidAuthExpiresAt()
    {
        $authExpiresAt = (new \DateTime())->add(new \DateInterval('PT1S'));
        $oauthToken = new OauthToken();
        $oauthToken->setAuthExpiresAt($authExpiresAt);

        $this->assertEquals($authExpiresAt, $oauthToken->getAuthExpiresAt());
    }

    /**
     * @param mixed $authExpiresAt
     *
     * @dataProvider nonDateTimeProvider
     */
    public function testInvalidTypeAuthExpiresAt($authExpiresAt)
    {
        $this->expectException(\TypeError::class);

        $oauthToken = new OauthToken();
        $oauthToken->setAuthExpiresAt($authExpiresAt);
    }

    /**
     * @param string $sessionHandle
     *
     * @dataProvider stringProvider
     */
    public function testValidSessionHandle($sessionHandle)
    {
        $oauthToken = new OauthToken();
        $oauthToken->setSessionHandle($sessionHandle);

        $this->assertEquals($sessionHandle, $oauthToken->getSessionHandle());
    }

    /**
     * @param mixed $sessionHandle
     *
     * @dataProvider nonStringTypeProvider
     */
    public function testInvalidTypeSessionHandle($sessionHandle)
    {
        $this->expectException(\TypeError::class);

        $oauthToken = new OauthToken();
        $oauthToken->setSessionHandle($sessionHandle);
    }

    public function validIdProvider()
    {
        return [
            [1],
            [999999999999999999],
            ['1'],
            ['abc'],
            ['a-b-c'],
            ['ABC'],
            [str_repeat('a_-.', 20)],
        ];
    }

    public function stringProvider()
    {
        return [
            ['a'],
            ['AbC'],
            ['1'],
            ['0'],
            ['123'],
        ];
    }

    public function nonStringTypeProvider()
    {
        return [
            'Integer 1' => [0],
            'Integer 2' => [1],
            'Float' => [.1],
            'Array' => [[]],
            'Object' => [new \stdClass()],
        ];
    }

    public function nonDateTimeProvider()
    {
        return array_merge(
            $this->nonStringTypeProvider(),
            ['String' => ['abc']]
        );
    }
}
