# Project abandoned due to incompatibility with nette 2.4

Please use `kdyby/monolog` instead

## Simple migration guide
0. Remove all configuration related to `salamek/raven-nette` && composer depedency from `composer.json`
1. Install `kdyby/monolog` and `sentry/sentry`

```bash
composer require kdyby/monolog
composer require sentry/sentry
```

2. Configure new extension

```yaml
sentry:
    dsn: 'YOUR_SENTRY_DSN'
extensions:
    monolog: Kdyby\Monolog\DI\MonologExtension
    
monolog:
    name: PROJECT_NAME
    hookToTracy: true
    registerFallback: true
    handlers:
        raven: Monolog\Handler\RavenHandler(Raven_Client(%sentry.dsn%))
    processors:
        - Monolog\Processor\GitProcessor
        - Monolog\Processor\WebProcessor
        - Kdyby\Monolog\Processor\PriorityProcessor
```
3. Profit...
For more informations consult `kdyby/monolog` documentation


# raven-nette

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=D8LQ4XTBLV3C4&lc=CZ&item_number=SalamekPplMyApi&currency_code=EUR)

This is nette implementation of sentry raven-php as logger service for tracy.

Tested with nette 2.2>, please report any bugs into Issues

PR's are welcomed!

## Installation and usage

### Installation via composer:

```bash
composer require salamek/raven-nette
```

### Usage

Register extension to your config.neon:

```yaml
extensions:
  sentryLogger: Salamek\RavenNette\DI\SentryLoggerExtension
```

And configure by setting:

```yaml
sentryLogger:
  dsn: 'YOUR_SENTRY_DSN'

  # Optional configuration values
  inDebug: false # bool: Log in debug mode ? default is false
  directory: null # string|null: Where to store log files ? default is Debugger::$logDirectory, null to disable
  email: null # string|null :Where to send email notifications ? default is Debugger::$email, null to disable
  options: [release: YOUR_RELEASE] # array :All options supported by getsentry/sentry-php
  context:
    user: true # Send logged in user information
```
List of all confuration options for [getsentry/sentry-php](https://github.com/getsentry/sentry-php#configuration)

### Alternative Usage

If you dont want to use DI, and/or be able to log errors as soon as posible use this approach

Put this code into your `app/bootstrap.php` after RobotLoader is initiated and `$configurator->enableDebugger` is called:

```php
// Initiate sentryLogger
new \Salamek\RavenNette\SentryLogger(
  'YOUR_SENTRY_DSN', //Sentry DSN
  false, //Log in DEBUG mode ? //You dont want that...
  null, //Set where do you want to store file log (Tracy\Debugger::$logDirectory | null | string)
  null, //Send email as usual logger ?   (Tracy\Debugger::$email | null | string | array )
  true,
  ['release' => 'YOUR_RELEASE'] //All options supported by getsentry/sentry-php
);
```

### Usage only with tracy

If you dont want use nette at all but only raven-nette and tracy... well you can!

```php
include('vendor/autoload.php');
use Tracy\Debugger;

Debugger::enable(Debugger::PRODUCTION);

new \Salamek\RavenNette\SentryLogger(
  'YOUR_SENTRY_DSN', //Sentry DSN
  false, //Log in DEBUG mode ? //You dont want that...
  null, //Set where do you want to store file log (Tracy\Debugger::$logDirectory | null | string)
  null, //Send email as usual logger ?   (Tracy\Debugger::$email | null | string | array )
  true,
  ['release' => 'YOUR_RELEASE'] //All options supported by getsentry/sentry-php
);

Debugger::log('My error', 'error');
```

And that should be everything...
