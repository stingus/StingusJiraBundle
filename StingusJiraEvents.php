<?php

namespace Stingus\JiraBundle;

/**
 * Class StingusJiraEvents.
 * Bundle events collection
 *
 * @package Stingus\JiraBundle
 */
class StingusJiraEvents
{
    /**
     * The OAUTH_TOKEN_GENERATE event occurs when an OauthToken is fully created.
     * Event dispatched is Stingus\JiraBundle\Event\OauthTokenGeneratedEvent
     */
    const OAUTH_TOKEN_GENERATE = 'stingus_jira.oauth_token.generate';
}
