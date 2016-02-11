
v1.1.1
------
Fixes issue when all Tracy events was send to Sentry, now only events of ERROR, EXCEPTION, CRITICAL and WARNING are send to Sentry (ignores DEBUG, INFO)

v1.1
----
First stable relase,
Removed need of manual connecting events from Tracy to raven-nette, so no need to use this code (this code is run in raven-nette __construct):

```
 // Add Fatal Error handler		
 \Tracy\Debugger::$onFatalError[] = function($e) use($sentryLogger)		
 {		
   $sentryLogger->onFatalError($e);		
 };		
 		
 // Add logger to tracy		
 Tracy\Debugger::setLogger($sentryLogger);
```

v1.0-alpha
----------
First testing release
