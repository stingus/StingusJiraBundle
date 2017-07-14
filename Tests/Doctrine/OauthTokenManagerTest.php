<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Stingus\JiraBundle\Doctrine\OauthTokenManager;
use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\Model\OauthTokenInterface;
use Stingus\JiraBundle\Tests\OauthTokenFactory;

class OauthTokenManagerTest extends TestCase
{
    public function testGetRepository()
    {
        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)->getMock();
        $repositoryMock = $this->getMockBuilder(ObjectRepository::class)->getMock();

        $objectManagerMock
            ->expects($this->exactly(1))
            ->method('getRepository')
            ->with(OauthTokenInterface::class)
            ->willReturn($repositoryMock);

        $oauthTokenManager = new OauthTokenManager($objectManagerMock, OauthTokenInterface::class);

        $this->assertSame($repositoryMock, $oauthTokenManager->getRepository());
    }

    public function testSaveToken()
    {
        $oauthToken = OauthTokenFactory::getBasicOauthToken();

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)->getMock();

        $objectManagerMock
            ->expects($this->exactly(1))
            ->method('persist')
            ->with($oauthToken);

        $objectManagerMock
            ->expects($this->exactly(1))
            ->method('flush');

        $oauthTokenManager = new OauthTokenManager($objectManagerMock, OauthTokenInterface::class);
        $oauthTokenManager->save($oauthToken);
    }
}
