<?php

namespace Stingus\JiraBundle\Event;

use Stingus\JiraBundle\Model\OauthTokenInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class OauthTokenGeneratedEvent.
 * Event dispatched when an OauthToken is generated
 *
 * @package Stingus\JiraBundle\Event
 */
class OauthTokenGeneratedEvent extends Event
{
    /** @var OauthTokenInterface */
    private $oauthToken;

    /**
     * OauthTokenGeneratedEvent constructor.
     *
     * @param OauthTokenInterface $oauthToken
     */
    public function __construct(OauthTokenInterface $oauthToken)
    {
        $this->oauthToken = $oauthToken;
    }

    /**
     * @return OauthTokenInterface
     */
    public function getOauthToken()
    {
        return $this->oauthToken;
    }
}
