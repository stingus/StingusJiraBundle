SSL certificate
===============

Each request send to a JIRA API endpoint must be signed using a private key. That's why you need to issue a certificate
and export its private and public key. You can do this in two ways:

Using the built-in Symfony command
----------------------------------

The following command will generate a certificate and the private / public keys:

.. code-block:: bash

    $ bin/console stingus_jira:generate:cert

You will be asked to enter the certificate details from the command line and the validity of the certificate (in days).
The files will be saved in the ``cert_path`` directory, according to your `config`_

.. _config: https://github.com/stingus/StingusJiraBundle/blob/master/Resources/doc/install.rst

Manually issuing the certificate
--------------------------------

If you'd like to use OpenSSL to generate the certificate, you can run the following commands:

.. code-block:: bash

    $ openssl genrsa -out jira_privatekey.pem 1024
    $ openssl req -newkey rsa:1024 -x509 -key jira_privatekey.pem -out jira_publickey.cer -days 365
    $ openssl pkcs8 -topk8 -nocrypt -in jira_privatekey.pem -out private.key
    $ openssl x509 -pubkey -noout -in jira_publickey.cer  > public.key

Copy the private.key and the public.key files in your project, at the location set in the ``cert_path`` config.
**Make sure** the files are named private.key and public.key.
