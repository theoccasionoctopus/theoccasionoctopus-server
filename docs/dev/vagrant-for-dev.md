# Vagrant for Developers


## There are 2 boxes

The setup provides 2 boxes, `app1` and `app2`. They are on the same network and can see each other.

This is useful if you ever want to test 2 instances sending messages to each other. If not, just use `app1` only.


## If first time to run dev box

Connect with:

    vagrant ssh app1

Create the file `.env.local` and edit the contents to:

```
MAILER_URL=smtp://localhost:1025
MAILER_FROM_EMAIL="devinstance@example.com"
MAILER_FROM_NAME="Dev Instance"
DATABASE_URL=postgresql://app:password@127.0.0.1:5432/app?serverVersion=10&charset=utf8
INSTANCE_SYSADMIN_EMAIL="sysadmin@example.com"
MESSENGER_TRANSPORT_DSN="amqp://guest:guest@localhost:5672/%2f/messages"
```

On the `app1` box also add:

```
INSTANCE_URL="http://192.168.50.11"
INSTANCE_NAME="Dev Instance 1"
```

On the `app2` box also add:

```
INSTANCE_URL="http://192.168.50.12"
INSTANCE_NAME="Dev Instance 2"
```

Then run:

    php /bin/composer.phar install
    yarn encore dev
    php bin/console   doctrine:migrations:migrate --no-interaction    
    php bin/console theocasionoctupus:load-country-data


## Access

You can then view:

* `app1` on http://192.168.50.11
* `app2` on http://192.168.50.12

You can view all emails sent by each server by going to port 8025 on those IP addresses.

## Rsync issues

Use 

    vagrant rsync

Use 

    vagrant rsync-auto
    
    
Copy changed files back from `app1`:
    
    scp vagrant@192.168.50.11:/vagrant/composer.json .
    scp vagrant@192.168.50.11:/vagrant/composer.lock .
    scp vagrant@192.168.50.11:/vagrant/package.json .
    scp vagrant@192.168.50.11:/vagrant/yarn.lock .
    scp vagrant@192.168.50.11:/vagrant/symfony.lock .
    scp -r vagrant@192.168.50.11:/vagrant/migrations .
    scp -r vagrant@localhost:/vagrant/config .
        
## Message Ques

Run consumer:

    php ./bin/console messenger:consume -vv --failure-limit=1


## PHP Tests

Put

    DATABASE_URL=postgresql://apptest:passwordtest@127.0.0.1:5432/apptest?serverVersion=10&charset=utf8
    MESSENGER_TRANSPORT_DSN="in-memory://"
    
into `.env.test.local`

Then run

    ./bin/phpunit

## Linting

To set up:

    mkdir --parents tools/php-cs-fixer
    /bin/composer.phar require --working-dir=tools/php-cs-fixer friendsofphp/php-cs-fixer==2.17.2
    
To run:

    tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src
    
To copy back from `app1`:

    scp -r vagrant@192.168.50.11:/vagrant/src .

## Drop the database and start again

Run:

    sudo su --login -c "psql -c \"DROP DATABASE app\"" postgres
    sudo su --login -c "psql -c \"CREATE DATABASE app WITH OWNER app ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres
    php bin/console --no-interaction doctrine:migrations:migrate
    php bin/console theocasionoctupus:load-country-data
 
 
 