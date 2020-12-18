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


## INSTANCE_NAME



```
INSTANCE_NAME="My Instance Name"
```


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

