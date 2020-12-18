# Vagrant for Developers


## If first time to run dev box

Connect with:

    vagrant ssh

Put

```
MAILER_URL=smtp://localhost:1025
MAILER_FROM_EMAIL="devinstance@example.com"
MAILER_FROM_NAME="Dev Instance"
DATABASE_URL=postgresql://app:password@127.0.0.1:5432/app?serverVersion=10&charset=utf8
INSTANCE_NAME="Dev Instance"
INSTANCE_SYSADMIN_EMAIL="sysadmin@example.com"
INSTANCE_URL="http://localhost:8080"
MESSENGER_TRANSPORT_DSN="amqp://guest:guest@localhost:5672/%2f/messages"
```

into `.env.local`, then run:

    yarn encore dev
    php bin/console   doctrine:migrations:migrate --no-interaction    
    php bin/console theocasionoctupus:load-country-data


## Access

The app should then be available on http://localhost:8080/

You can view all emails sent at http://localhost:8025


## Rsync issues

Use 

    vagrant rsync

Use 

    vagrant rsync-auto
    
    
Copy changed files back
    
    scp -P 2222 vagrant@localhost:/vagrant/composer.json .
    scp -P 2222 vagrant@localhost:/vagrant/composer.lock .
    scp -P 2222 vagrant@localhost:/vagrant/package.json .
    scp -P 2222 vagrant@localhost:/vagrant/yarn.lock .
    scp -P 2222 vagrant@localhost:/vagrant/symfony.lock .
    scp -P 2222 -r vagrant@localhost:/vagrant/migrations .
    scp -P 2222 -r vagrant@localhost:/vagrant/config .
        
## Message Ques

Run consumer:

    php ./bin/console messenger:consume


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
    
To copy back:

    scp -P 2222 -r vagrant@localhost:/vagrant/src .

## Drop the database and start again

Run:

    sudo su --login -c "psql -c \"DROP DATABASE app\"" postgres
    sudo su --login -c "psql -c \"CREATE DATABASE app WITH OWNER app ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres
    php bin/console   doctrine:migrations:migrate 
    php bin/console theocasionoctupus:load-country-data
 
 
 