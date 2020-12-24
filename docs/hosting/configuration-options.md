# Configuration Options

Below are a list of environmental variables you can set, either the usual way or via the `.env.local` file.


## APP_ENV

This should always be set to `prod`.

```
APP_ENV=prod
```

## APP_SECRET


```
APP_SECRET="A-RANDOM-STRING-SET-ONCE-AND-DONT-CHANGE"
```


## DATABASE_URL



```
DATABASE_URL=postgresql://occ_oct_app:ENTER-PASSWORD-HERE@127.0.0.1:5432/occ_oct_app?serverVersion=12&charset=utf8
```


## DEFAULT_COUNTRY



```
DEFAULT_COUNTRY=GB
```


## DEFAULT_TIMEZONE



```
DEFAULT_TIMEZONE=Europe/London
```

## INSTANCE_FEDERATION

```
INSTANCE_FEDERATION=on
INSTANCE_FEDERATION=off
```

If `off`, this disables the WebFinger and Activitypub inbox and outbox API's.

This has the effect of disabling all federation features to other Occasion Octopus or other Activitypub servers.

Turning this off and on repeatedly will cause problems; it should either:

* Always be `off`, if your instance never wants to federate.
* Almost always be `on`, and only turned `off` very occasionally for moderation or security problems.

(However the API, with it's iCal and JSON feeds, is not disabled by this setting.)

## INSTANCE_NAME



```
INSTANCE_NAME="My Instance Name"
```


## INSTANCE_READ_ONLY

```
INSTANCE_READ_ONLY=on
INSTANCE_READ_ONLY=off
```

Should normally be `off`. Only turn `on` for brief periods for maintenance, moderation or security problems. 

Before turning `on`, make sure any workers are fully stopped.

TODO This only disables a few places to write at the moment; it should disable them all!

## INSTANCE_SYSADMIN_EMAIL



```
INSTANCE_SYSADMIN_EMAIL="hello@example.com"
```


## INSTANCE_URL



```
INSTANCE_URL="https://mytestserver.net"
```


There should not be a slash at the end.

## MAILER_URL



```
MAILER_URL=smtp://localhost:1025
```


## MAILER_FROM_EMAIL



```
MAILER_FROM_EMAIL="theoccocc@example.com"
```


## MESSENGER_TRANSPORT_DSN

```
MESSENGER_TRANSPORT_DSN="amqp://guest:guest@localhost:5672/%2f/messages"
```


## USER_REGISTER_INSTANCE_PASSWORD




```
USER_REGISTER_INSTANCE_PASSWORD="please"
```

