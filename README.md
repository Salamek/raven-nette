# raven-nette

This is nette implementation of sentry raven-php as logger service for tracy.

Tested with nette 2.3>, please report any bugs into Issues

WARNING: This project is alpha version, keep it in mind and report Issues send PR's

## Installation and usage

### Installation via composer:

```json
{
  "require":{
    "salamek/raven-nette"
  }
}
```

### Usage

Put this code into your `app/bootstrap.php` after RobotLoader is initiated and `$configurator->enableDebugger` is called:
```php
// Initiate sentryLogger
$sentryLogger = new \Salamek\sentryNetteLogger(
      'YourSentryDSN', //Sentry DSN
      false, //Log in DEBUG mode ? //You dont want that...
      null, //Set where do you want to store file log (Tracy\Debugger::$logDirectory | null | string)
      null //Send email as usual logger ?   (Tracy\Debugger::$email | null | string | array )
      );

// Add Fatal Error handler
\Tracy\Debugger::$onFatalError[] = function($e) use($sentryLogger)
{
  $sentryLogger->onFatalError($e);
};

// Add logger to tracy
Tracy\Debugger::setLogger($sentryLogger);
```

And that should be everything...
