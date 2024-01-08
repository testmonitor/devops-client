# TestMonitor Azure DevOps Client

[![Latest Stable Version](https://poser.pugx.org/testmonitor/devops-client/v/stable)](https://packagist.org/packages/testmonitor/devops-client)
[![CircleCI](https://img.shields.io/circleci/project/github/testmonitor/devops-client.svg)](https://circleci.com/gh/testmonitor/devops-client)
[![Travis Build](https://travis-ci.com/testmonitor/devops-client.svg?branch=master)](https://app.travis-ci.com/github/testmonitor/devops-client)
[![Code Coverage](https://scrutinizer-ci.com/g/testmonitor/devops-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/testmonitor/devops-client/?branch=master)
[![Code Quality](https://scrutinizer-ci.com/g/testmonitor/devops-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/testmonitor/devops-client/?branch=master)
[![StyleCI](https://styleci.io/repos/223973950/shield)](https://styleci.io/repos/223973950)
[![License](https://poser.pugx.org/testmonitor/devops-client/license)](https://packagist.org/packages/testmonitor/devops-client)

This package provides a very basic, convenient, and unified wrapper for [Microsoft Azure DevOps](https://azure.microsoft.com/en-us/services/devops/).

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Examples](#examples)
- [Tests](#tests)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

To install the client you need to require the package using composer:

	$ composer require testmonitor/devops-client

Use composer's autoload:

```php
require __DIR__.'/../vendor/autoload.php';
```

You're all set up now!

## Usage

This client only supports **oAuth authentication**. You'll need an Azure DevOps application to proceed. If you haven't done so,
please read up with the [Azure DevOps authentication docs](https://docs.microsoft.com/en-us/azure/devops/integrate/get-started/authentication/oauth?view=azure-devops) on how
to create an application.

When your DevOps application is up and running, start with the oAuth authorization:

```php
$oauth = [
    'clientId' => '12345',
    'clientSecret' => 'abcdef',
    'redirectUrl' => 'https://redirect.myapp.com/',
];

$devops = new \TestMonitor\DevOps\Client($oauth);

header('Location: ' . $devops->authorizationUrl());
exit();
```

This will redirect the user to a page asking confirmation for your app getting access to Azure DevOps. Make sure your redirectUrl points
back to your app. This URL should point to the following code:

```php
$oauth = [
    'clientId' => '12345',
    'clientSecret' => 'abcdef',
    'redirectUrl' => 'https://redirect.myapp.com/',
];

$devops = new \TestMonitor\DevOps\Client($oauth);

$token = $devops->fetchToken($_REQUEST['code']);
```

When everything went ok, you should have an access token (available through Token object). It will be valid for **one hour**.
After that, you'll have to refresh the token to regain access:

```php
$oauth = ['clientId' => '12345', 'clientSecret' => 'abcdef', 'redirectUrl' => 'https://redirect.myapp.com/'];
$token = new \TestMonitor\DevOps\AccessToken('eyJ0...', '0/34ccc...', 1574601877); // the token you got last time
$organization = 'MyOrg';

$devops = new \TestMonitor\DevOps\Client($oauth, $organization, $token);

if ($token->expired()) {
    $newToken = $devops->refreshToken();
}
```

The new token will be valid again for the next hour.

## Examples

Get a list of Azure DevOps accounts:

```php
$accounts = $devops->accounts();
```

Or creating a work item, for example (using a work item type 'Bug' and example project with id 12345):

```php
$workItem = $devops->createWorkItem(new \TestMonitor\DevOps\Resources\WorkItem([
    'title' => 'Summary of the item',
    'description' => 'Some description',
    'workItemType' => 'Bug',
    'stepsToReproduce' => 'Repro steps',
]), '12345');
```

## Tests

The package contains integration tests. You can run them using PHPUnit.

    $ vendor/bin/phpunit

## Changelog

Refer to [CHANGELOG](CHANGELOG.md) for more information.

## Contributing

Refer to [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

## Credits

* **Thijs Kok** - *Lead developer* - [ThijsKok](https://github.com/thijskok)
* **Stephan Grootveld** - *Developer* - [Stefanius](https://github.com/stefanius)
* **Frank Keulen** - *Developer* - [FrankIsGek](https://github.com/frankisgek)
* **Muriel Nooder** - *Developer* - [ThaNoodle](https://github.com/thanoodle)

## License

The MIT License (MIT). Refer to the [License](LICENSE.md) for more information.
