[![Build Status](https://travis-ci.org/stingus/StingusJiraBundle.svg?branch=master)](https://travis-ci.org/stingus/StingusJiraBundle)
[![Code Climate](https://codeclimate.com/github/stingus/StingusJiraBundle/badges/gpa.svg)](https://codeclimate.com/github/stingus/StingusJiraBundle)
[![Test Coverage](https://codeclimate.com/github/stingus/StingusJiraBundle/badges/coverage.svg)](https://codeclimate.com/github/stingus/StingusJiraBundle/coverage)

# StingusJiraBundle
This bundle connects your Symfony application to one or more Atlassian JIRA instances, allowing you to make API requests
to those instances.

## When you should use this bundle
If you need to use the JIRA REST APIs for one or more JIRA instances, the bundle exposes all the methods needed to
make API requests, using the OAuth 1.0a protocol.

## When you should not use this bundle
This bundle is not using JIRA as an authentication provider for your application users. There are other solutions doing
just that, like [HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle).

## Features
- SSL certificate generation
- Get the OAuth tokens for each JIRA instance used in your application
- OAuth authorization controller, dealing with the OAuth "dance" between your application (as a consumer) and the JIRA
instance (as describe in the [official JIRA docs](https://developer.atlassian.com/cloud/jira/platform/jira-rest-api-oauth-authentication/))
- JIRA API client, which can be used for all API requests sent to the JIRA instance
- OAuth token storage (only Doctrine ORM for now). You can, however, manage the tokens by catching the
Stingus/Jira/Event/OauthTokenGeneratedEvent

## Prerequisites
- PHP >= 7.0 (openssl extension required)
- Symfony >= 3.3

## Documentation
See the [bundle documentation](https://github.com/stingus/StingusJiraBundle/blob/master/Resources/doc/index.rst)
for installation and usage.
