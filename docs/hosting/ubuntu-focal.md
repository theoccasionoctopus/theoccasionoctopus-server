# Ubuntu 20.04 Focal LTS

This guides you through setting up the app on a new server, with:

* The app as the ONLY site on the server
* The database on the same server
* Use of an external email sending service
* LetsEncrypt for SSL

This guide assumes decent knowledge of Linux server admin via the command line.

## Set up server for first time.

### Get an email sending provider

TODO

### Start with your server

Create server.

SSH in as root.

At this point you may want to configure some common security things, for example:

* Locking down SSH access.
* Enabling automatic updates.

This is outwith the scope of this guide.

### DNS entries

Set up a domain name or subdomain with DNS records correctly set up to point to the server.

### Pick a locale

We are going to set up a locale for us to use. You may choose a different one for your country, but if so, change the database creation instructions later.

    echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen
    locale-gen

### Create User

Add a user for us to use. Set a random password. You don't need to note it anywhere:

    adduser occ_oct

### Installing needed packages

Next install needed packages:

    apt-get update
    DEBIAN_FRONTEND=noninteractive apt-get install -y postgresql apache2 php-mbstring php-gd php php-curl php-pgsql git php-intl curl zip php7.4-fpm php-zip php-xml php-zip certbot python3-certbot-apache
    
    curl -sL https://deb.nodesource.com/setup_12.x | sudo -E bash -
    apt-get install -y nodejs
    
    curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
    apt-get update && apt-get install -y yarn
    
To install composer, run:

    mkdir -p /bin
    cd /bin    
    
Then go to https://getcomposer.org/download/ and copy and paste the script commands from there.
    
### Get the app
    
Check out the source code:

    su -c "git clone https://github.com/theoccasionoctopus/theoccasionoctopus-server.git /home/occ_oct/software" occ_oct

TODO when we start doing releases, update instructions to check out a specific version by git tag

### Create the database

Generate a random password to use for the database user. Keep a note of it handy for the rest of this guide, but you won't need it saved afterwards.

Create the database, replacing the database password:

    sudo su --login -c "psql -c \"CREATE USER occ_oct_app WITH PASSWORD 'ENTER-PASSWORD-HERE';\"" postgres
    sudo su --login -c "psql -c \"CREATE DATABASE occ_oct_app WITH OWNER occ_oct_app ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres

### Configure the app

Create `/home/occ_oct/software/.env.local` with the contents (edited as appropriate):

```
MAILER_URL=smtp://localhost:1025
MAILER_FROM_EMAIL="theoccocc@example.com"
DATABASE_URL=postgresql://occ_oct_app:ENTER-PASSWORD-HERE@127.0.0.1:5432/occ_oct_app?serverVersion=12&charset=utf8
INSTANCE_NAME="My Instance Name"
INSTANCE_URL="https://mytestserver.net"
APP_ENV=prod
APP_SECRET="A-RANDOM-STRING-SET-ONCE-AND-DONT-CHANGE"
USER_REGISTER_INSTANCE_PASSWORD="please"
DEFAULT_COUNTRY=GB
DEFAULT_TIMEZONE=Europe/London
```

### Install Libraries
    
Installing libraries:

    cd /home/occ_oct/software
    su -c "npm install" occ_oct
    su -c "yarn install" occ_oct
    su -c "/bin/composer.phar install" occ_oct
    su -c "yarn encore production" occ_oct

### Set up the database

We can now run the app commands to set up the database:

    cd /home/occ_oct/software
    su -c "./bin/console doctrine:migrations:migrate --no-interaction" occ_oct
    su -c "./bin/console theocasionoctupus:load-country-data" occ_oct
    
### Set up the webserver

Set up the apache config by editing the file `/etc/apache2/sites-available/001-occ-oct.conf` with the contents:

    <VirtualHost *:80>
        ServerName SITE-DOMAIN-HERE
        # Add ServerAlias too if you have any extra ones
    
        DocumentRoot /home/occ_oct/software/public
        <Directory /home/occ_oct/software/public>
            AllowOverride None
            Require all granted
 
            FallbackResource /index.php
        </Directory>
    
        <Directory /home/occ_oct/software/public/build>
            FallbackResource disabled
        </Directory>
        
        SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
        
        ErrorLog /var/log/apache2/occ_oct_error.log
        CustomLog /var/log/apache2/occ_oct_access.log combined
    </VirtualHost>

Run:

    a2ensite 001-occ-oct
    a2dissite 000-default
    a2dismod php7.4 
    a2dismod mpm_prefork
    a2enmod mpm_event proxy_fcgi setenvif
    systemctl start php7.4-fpm
    systemctl enable php7.4-fpm
    a2enconf php7.4-fpm
    chmod o+rx /home/occ_oct/
    chmod o+rx /home/occ_oct/software/
    chmod o+rx /home/occ_oct/software/public/
    chown -R www-data:www-data /home/occ_oct/software/var
    systemctl reload apache2

### Set up cron

Edit the cron entries as the occ_oct user.

    su -c "crontab -e" occ_oct
    
Add the following entries:

```
0 6 * * * cd /home/occ_oct/software; ./bin/console theocasionoctupus:download-import-content
30 6 * * * cd /home/occ_oct/software; ./bin/console theocasionoctupus:download-remote-user-content
```

### Congratulations! 

At this stage you should have a site that basically works at the URL you have picked.

Test it loads by going to the address in a web browser. You should see the front page of the site.

Test email sending works: run the command:

    TODO

But there is still more to do to have a good and safe install - read on.
 
### SSL 

(You may need to skip this step, if you are running an internal site or some place where verifying an SSL cert will be difficult.)

Run:

    certbot --apache

And follow prompts. We suggest picking "Redirect - Make all requests redirect to secure HTTPS access.".

After doing so, you may need to edit `/home/occ_oct/software/.env.local` and change the `INSTANCE_URL` value.

### Enable HTTP2

(Note we can ONLY do this if we enabled SSL successfully. If SSL was skipped, this step must also be skipped.)

Run:

    a2enmod http2
    
Edit `/etc/apache2/sites-enabled/001-occ-oct-le-ssl.conf` and just below the `<VirtualHost *:443>` line add:

    Protocols h2 http/1.1
    
Then restart:
    
    /etc/init.d/apache2 restart

### Set up some admin users

TODO


### Misc

Set up a pgpass file and a db command alias.

    echo "localhost:5432:occ_oct_app:occ_oct_app:ENTER-PASSWORD-HERE" > /home/occ_oct/.pgpass
    chown occ_oct:occ_oct /home/occ_oct/.pgpass
    chmod 0600 /home/occ_oct/.pgpass
    echo "alias db='psql -U occ_oct_app occ_oct_app -hlocalhost'" >> /home/occ_oct/.bashrc

### Log Rotate

Create the file `/etc/logrotate.d/occ_oct_app` and set the contents:

```
/home/occ_oct/software/var/log/*.log {
    daily
    missingok
    rotate 365
    compress
    delaycompress
    notifempty
    create 664 www-data www-data
    su www-data www-data
}
```

### Set up backups

Create the file `/home/occ_oct/backup.sh` and set the contents:

```
#!/bin/bash
set -e
pg_dump -w -h localhost  -U occ_oct_app -d occ_oct_app -f /home/occ_oct/backups/occ_oct_backup_`date +%d`.sql
```

Run


```
su -c "mkdir /home/occ_oct/backups/" occ_oct
chown occ_oct /home/occ_oct/backup.sh
chmod 744 /home/occ_oct/backup.sh   
```
 
Edit the cron entries as the occ_oct user.
 
     su -c "crontab -e" occ_oct
     
Add the following entry:

    0 1 * * * /home/occ_oct/backup.sh > /home/occ_oct/backup.log 2>&1
    
The directory `/home/occ_oct/backups/` will now contain daily backups of the database.

You must find a way to back this directory up. Some hosts will take regular snap shots of your machines disks, or use one of many Linux backup tools.

You may also want to back up the following directories - these contain log files:

* `/home/occ_oct/software/var/log/`
* `/var/log/apache2/`

## Handy tips

### Easy command line access to database

When you are logged in as the user occ_oct, you can run `db` to get a database shell.

**Be aware this can make changes - be careful!**

## Update to latest version

Log into server and run:

    cd /home/occ_oct/software
    su -c "git pull" occ_oct
    chown -R occ_oct:occ_oct /home/occ_oct/software/var
    su -c "/bin/composer.phar install" occ_oct
    su -c "./bin/console doctrine:migrations:migrate --no-interaction" occ_oct
    su -c "./bin/console theocasionoctupus:load-country-data" occ_oct
    chown -R www-data:www-data /home/occ_oct/software/var
    su -c "npm install" occ_oct
    su -c "yarn install" occ_oct
    su -c "yarn encore production" occ_oct

TODO when we start doing releases, update instructions to check out a specific version by git tag

Note the cron entries that should be added (see above) are very likely to change with new version of the software! Check above and edit as needed.



