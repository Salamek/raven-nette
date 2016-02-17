# raven-nette

This is nette implementation of sentry raven-php as logger service for tracy.

Tested with nette 2.3>, please report any bugs into Issues

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
  dsn: 'YOUR_SENTRY_DNS'

  # Optional configuration values
  inDebug: false # bool: Log in debug mode ? default is false
  directory: null # string|null: Where to store log files ? default is Debugger::$logDirectory, null to disable
  email: null # string|null :Where to send email notifications ? default is Debugger::$email, null to disable
```

### Alternative Usage

If you dont want to use DI, and/or be able to log errors as soon as posible use this approach

Put this code into your `app/bootstrap.php` after RobotLoader is initiated and `$configurator->enableDebugger` is called:
```php
// Initiate sentryLogger
new \Salamek\RavenNette\SentryLogger(
  'YourSentryDSN', //Sentry DSN
  false, //Log in DEBUG mode ? //You dont want that...
  null, //Set where do you want to store file log (Tracy\Debugger::$logDirectory | null | string)
  null //Send email as usual logger ?   (Tracy\Debugger::$email | null | string | array )
);
```

And that should be everything...
