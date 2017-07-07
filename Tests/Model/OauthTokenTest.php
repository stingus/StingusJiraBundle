<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Model;

use Stingus\JiraBundle\Exception\ModelException;
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
     * @param string $consumerKey
     * @param string $exceptionMessage
     *
     * @dataProvider invalidConsumerKeyProvider
     */
    public function testInvalidConsumerKey($consumerKey, $exceptionMessage)
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage($exceptionMessage);

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
     * @param string $baseUrl
     * @param string $exceptionMessage
     *
     * @dataProvider invalidBaseUrlProvider
     */
    public function testInvalidBaseUrl($baseUrl, $exceptionMessage)
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage($exceptionMessage);

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
     * @param mixed $id
     * @param string $exceptionMessage
     *
     * @dataProvider invalidIdProvider
     */
    public function testInvalidId($id, $exceptionMessage)
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $oauthToken = new OauthToken();
        $oauthToken->setId($id);
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

    public function testEmptyVerifier()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Verifier must not be empty');

        $oauthToken = new OauthToken();
        $oauthToken->setVerifier('');
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

    public function testEmptyToken()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Token must not be empty');

        $oauthToken = new OauthToken();
        $oauthToken->setToken('');
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

    public function testEmptyTokenSecret()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Token secret must not be empty');

        $oauthToken = new OauthToken();
        $oauthToken->setTokenSecret('');
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

    public function testExpiresAtInPast()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Expire date must be in the future');

        $expiresAt = (new \DateTime())->sub(new \DateInterval('PT0S'));
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

    public function testAuthExpiresAtInPast()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Authorization expire date must be in the future');

        $authExpiresAt = (new \DateTime())->sub(new \DateInterval('PT0S'));
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

    public function testEmptySessionHandle()
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('Session handle must not be empty');

        $oauthToken = new OauthToken();
        $oauthToken->setSessionHandle('');
    }

    public function invalidConsumerKeyProvider()
    {
        return [
            'Empty consumer key' => [
                '',
                'Consumer key length must be between 0 and 255 characters',
            ],
            'Consumer key too long' => [
                str_repeat('a', 256),
                'Consumer key length must be between 0 and 255 characters',
            ],
        ];
    }

    public function invalidBaseUrlProvider()
    {
        return [
            'Invalid base URL 1' => [
                'example',
                'Base URL is invalid',
            ],
            'Invalid base URL 2' => [
                'example.com',
                'Base URL is invalid',
            ],
        ];
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

    public function invalidIdProvider()
    {
        return [
            'Zero Id' => [
                0,
                'An integer ID must be greater than 0'
            ],
            'Negative Id' => [
                -1,
                'An integer ID must be greater than 0'
            ],
            'Float Id' => [
                .1,
                'The ID must be a string or a positive integer'
            ],
            'Array Id' => [
                [],
                'The ID must be a string or a positive integer'
            ],
            'Object Id' => [
                new \stdClass(),
                'The ID must be a string or a positive integer'
            ],
            'Empty string Id' => [
                '',
                'A string ID must not be empty'
            ],
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
