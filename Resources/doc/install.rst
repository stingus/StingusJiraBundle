Install StingusJiraBundle
=========================

Step 1: Download the bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. code-block:: bash

    $ composer require stingus/jira-bundle

Step 2: Enable the bundle
~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Stingus\JiraBundle\StingusJiraBundle(),
            // ...
        );
    }

Step 3: Import the routes
~~~~~~~~~~~~~~~~~~~~~~~~~

Add the bundle routes in your application:

.. code-block:: yaml

    // app/config/routing.yml

    stingus_jira:
      resource: "@StingusJiraBundle/Resources/config/routing.yml"

Step 4: Implement your own OauthTokenInterface class
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This class serves as an OAuth token model for all API requests. You must either:

Step 4.1: Extend Stingus\\JiraBundle\\Model\\AbstractOauthToken (recommended)
-----------------------------------------------------------------------------

This abstract class is a base token model, providing basic methods for handling an OAuth token.

If you plan to use Doctrine ORM, it is recommended to create this class in the ``Entity`` directory:

.. code-block:: php

    <?php
    // src/AppBundle/Entity/JiraToken.php

    namespace AppBundle\Entity;

    use Stingus\JiraBundle\Model\AbstractOauthToken;
    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity()
     */
    class JiraToken extends AbstractOauthToken
    {
        // Add your entity additional properties and methods
        ...
    }

This way, together with ORM mapping configuration below, the bundle will save in your DB the OAuth tokens received from
JIRA and will retrieve them when needed using the OauthTokenManager.

Step 4.2: Implement your own Stingus\\JiraBundle\\Model\\OauthTokenInterface
----------------------------------------------------------------------------

If you prefer to build your own token class from scratch, you just need to implement
``Stingus\JiraBundle\Model\OauthTokenInterface``.

Step 5: Update your database schema (optional)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This step is required only if you intend to use the built-in OAuth token storage feature. If you're using Doctrine ORM
in your application, you can enable the storage of OAuth tokens in the database, as described in the configuration
section below (see: mapping).

.. code-block:: bash

    $ bin/console doctrine:schema:update --force

Configuration
=============

Here's the full config section:

.. code-block:: yaml

    // app/config/config.yml

    stingus_jira:
      mapping:
        driver: orm
        model_manager_name: default
      oauth_token_class: AppBundle\Entity\JiraToken
      cert_path: var/certs
      redirect_url: http://example.com/redirect

Mapping
~~~~~~~

OAuth tokens, generated for each JIRA instance that your application will connect to, can be persisted using
Doctrine ORM. Since you need the tokens to make API requests to JIRA, you'll definitely need a way to persist them.

**Note**: there's another way to persist the tokens, using your own storage layer.
Check the `Usage`_ section to see how to use the Stingus/Jira/Event/OauthTokenGeneratedEvent event.

If the ``mapping`` config key is present, you need to set the ``driver`` config as well, with the only supported value
for now: ``orm``. If you want to use a non-default entity manager, you need to pass it to the ``model_manager_name``
config, otherwise it can be omitted.

To disable the token persistence, just remove the ``mapping`` key from the config.

Other config options
~~~~~~~~~~~~~~~~~~~~

``oauth_token_class``: this is the FQCN created in step 4.1 or 4.2 above.

.. code-block:: yaml

    stingus_jira:
      oauth_token_class: AppBundle\Entity\MyOauthToken

``cert_path``: is the path where the SSL certificate and keys are stored. It is a relative path to the project root and
it defaults to ``var/certs``. This path will be used when generating the certificate and when an API request is sent to
JIRA, since it needs to be signed with the private key.

``redirect_url``: after the user authorizes your application with his JIRA instance, the browser will redirect to this
URL

Next steps
==========

You might want to jump to `Generating the SSL certificate for your application`_ or even the `Usage`_ section.

.. _Generating the SSL certificate for your application: https://github.com/stingus/StingusJiraBundle/blob/master/Resources/doc/certificate.rst
.. _Usage: https://github.com/stingus/StingusJiraBundle/blob/master/Resources/doc/usage.rst
