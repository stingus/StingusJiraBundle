<?php

declare(strict_types = 1);

namespace Stingus\JiraBundle\Tests\Event;

use Stingus\JiraBundle\Event\OauthTokenGeneratedEvent;
use PHPUnit\Framework\TestCase;
use Stingus\JiraBundle\Model\OauthTokenInterface;

class OauthTokenGeneratedEventTest extends TestCase
{
    public function testCreateEvent()
    {
        $oauthTokenMock = $this->getMockBuilder(OauthTokenInterface::class)->getMock();
        $event = new OauthTokenGeneratedEvent($oauthTokenMock);

        $this->assertSame($oauthTokenMock, $event->getOauthToken());
    }
}
