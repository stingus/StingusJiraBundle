Usage
=====

***Make sure** you `issued`_ the private and public keys before going further

.. _issued: https://github.com/stingus/StingusJiraBundle/blob/master/Resources/doc/certificate.rst
.. _certificate generation doc: https://github.com/stingus/StingusJiraBundle/blob/master/Resources/doc/certificate.rst

Application authorization
-------------------------

Before sending any requests to JIRA API endpoints, you application needs to be authorized. Here's the authorization
workflow:

.. image:: images/jira_oauth_flow.png

The workflow will be soon detailed, but first you need to deal with:

JIRA configuration
~~~~~~~~~~~~~~~~~~

1. Log in to your JIRA instance
2. Click the settings button in the top right corner (cog icon) and select "Applications"
3. On the left column click "Application links", under "Integrations"
4. Enter your application URL and click "Create new link". Click continue in the pop-up dialog
5. Fill in the required fields:
    i. Application name: choose whatever seems appropriate
    ii. Application type: leave "Generic Application" selected
    iii. Check "Create incoming link"
    iv. Leave the other fields empty and click "Continue"
6. Fill in the incoming parameters fields:
    i. Consumer key: create a unique string and save it, you'll need it later
    ii. Consumer name: (can be your application name)
    iii. Public key: copy & paste the contents of the public.key file (see `certificate generation doc`_)
    iv. Click "Continue" and you're done with the JIRA configuration

Learn more about `JIRA configuration`_.

.. _JIRA configuration: https://developer.atlassian.com/cloud/jira/platform/jira-rest-api-oauth-authentication/

Starting the authorization process
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

After configuring your JIRA instance to accept connections from your application, you can start the authorization
process. In one of your application's controllers issue a redirect to the following route:

.. code-block:: php

    <?php
    // src/AppBundle/Controller/DefaultController.php

    public function startAuthorizationAction()
    {
        return $this->forward(
            'StingusJiraBundle:Oauth:connect',
            [
                'consumerKey' => 'your_consumer_key',
                'baseUrl' => 'https://example.atlassian.net',
            ]
        );
    }

This will forward the request to the first step of the authorization process, using a controller exposed by this bundle.
You'll need to provide the following route parameters:

``consumerKey``: the consumer key used in JIRA configuration step 6i

``baseUrl``: the base URL of your JIRA instance (eg. https://example.atlassian.net)

The browser will then be redirected to the application authorization page of JIRA. After the user
authorizes your application, il will be redirected to the ``redirect_url`` set in your config.yml.

Saving the OAuth token for JIRA requests
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you choose to use the built-in ORM persistence (see `Installation & configuration`_), right after the user
authorizes your application, the token is persisted in the DB so it can be used when issuing new requests to JIRA.

.. _Installation & configuration: https://github.com/stingus/StingusJiraBundle/blob/master/Resources/doc/install.rst

Catching the OauthTokenGeneratedEvent
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you're not using Doctrine ORM, you'll need a way to catch the OAuth token and persist it somehow. When a valid OAuth
token is received from JIRA, the bundle emits a ``Stingus\Jira\Event\OauthTokenGeneratedEvent`` event which will contain
the token. To catch this event and process the token, you need to configure your own Symfony listener / subscriber.
Here's a subscriber example catching this event:

.. code-block:: php

    <?php
    // src/AppBundle/EventListener/OauthTokenListener.php

    namespace AppBundle\EventListener;

    use Stingus\JiraBundle\Event\OauthTokenGeneratedEvent;
    use Stingus\JiraBundle\StingusJiraEvents;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class OauthTokenListener implements EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            return [
                StingusJiraEvents::OAUTH_TOKEN_GENERATE => [
                    ['onTokenGenerate', 0],
                ],
            ];
        }

        public function onTokenGenerate(OauthTokenGeneratedEvent $event)
        {
            $oauthToken = $event->getOauthToken();

            // Persist the $oauthToken
        }
    }

Configure the listener:

.. code-block:: yaml

    services:
      app_bundle.oauth.listener:
        class: AppBundle\EventListener\OauthTokenListener
        tags:
           - { name: kernel.event_subscriber }

Using your own OAuthTokenInterface implementation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you want to build your own OAuthToken model, instead of using ``Stingus\JiraBundle\Model\OauthToken``, your model must
implement ``Stingus\JiraBundle\Model\OauthTokenInterface``. After you create it, make sure you set it in the config:

.. code-block:: yaml

    stingus_jira:
      ...
      oauth_token_class: Your\Own\OAuthToken

Making JIRA requests
--------------------

After you have persisted the OAuth token, you can now make requests to JIRA API. For example, in a controller:

.. code-block:: php

    <?php
    // src/AppBundle/Controller/DefaultController.php

    public function getStoryAction($consumerKey, $storyId)
    {
        // Retrieve the OAuth token from storage, using the provided OAuthTokenManager
        $oauthToken = $this->get(OauthTokenManager::SERVICE_ID)->findByConsumerKey($consumerKey);

        // Make a JIRA request using the token
        $story = $this->get(JiraRequest::SERVICE_ID)->get($oauthToken, '/rest/api/latest/issue/'.$storyId);

        return new Response($story->getBody()->getContents());
    }

The ``Stingus\JiraBundle\Request\JiraRequest`` offers just get() and post() methods for now, it will soon have all the REST
methods available.

That's it! Check the `documentation for JIRA API`_ to learn more about the endpoints.

.. _documentation for JIRA API: https://developer.atlassian.com/cloud/jira/platform/jira-cloud-platform-rest-api/