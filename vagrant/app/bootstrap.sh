#!/usr/bin/env bash

#--------------------------------------------  Good Bash Script

set -e

#--------------------------------------------  Locale

echo "en_GB.UTF-8 UTF-8" >> /etc/locale.gen

locale-gen

#--------------------------------------------  Install

apt-get update
DEBIAN_FRONTEND=noninteractive apt-get install -y postgresql apache2 php7.4-fpm php-mbstring php-gd php php-curl php-pgsql git php-intl curl zip php-zip php-xml rabbitmq-server php-amqp

#--------------------------------------------  Logs

mkdir /logs
chown www-data:www-data  /logs

#--------------------------------------------  Composer

mkdir -p /bin
cd /bin
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

#--------------------------------------------  Apache

cp /vagrant/vagrant/app/apache.conf /etc/apache2/sites-enabled/000-default.conf

a2enmod rewrite
a2dismod mpm_prefork
a2enmod mpm_event proxy_fcgi setenvif
systemctl start php7.4-fpm
systemctl enable php7.4-fpm
a2enconf php7.4-fpm

/etc/init.d/apache2 restart

#--------------------------------------------  Databases

sudo su --login -c "psql -c \"CREATE USER apptest WITH PASSWORD 'passwordtest';\"" postgres
sudo su --login -c "psql -c \"CREATE DATABASE apptest WITH OWNER apptest ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres

sudo su --login -c "psql -c \"CREATE USER app WITH PASSWORD 'password';\"" postgres
sudo su --login -c "psql -c \"CREATE DATABASE app WITH OWNER app ENCODING 'UTF8'  LC_COLLATE='en_GB.UTF-8' LC_CTYPE='en_GB.UTF-8'  TEMPLATE=template0 ;\"" postgres


#--------------------------------------------  MailHog Service

wget -O /bin/mailhog https://github.com/mailhog/MailHog/releases/download/v1.0.0/MailHog_linux_amd64
chmod a+x /bin/mailhog
cp /vagrant/vagrant/app/mailhog.service /etc/systemd/system/mailhog.service
chmod u+x /etc/systemd/system/mailhog.service
systemctl enable mailhog
systemctl start mailhog

#--------------------------------------------  Shell Script

echo "alias db='psql -U app app -hlocalhost'" >> /home/vagrant/.bashrc
echo "localhost:5432:app:app:password" > /home/vagrant/.pgpass
chown vagrant:vagrant /home/vagrant/.pgpass
chmod 0600 /home/vagrant/.pgpass

echo "cd /vagrant" >> /home/vagrant/.bashrc

#--------------------------------------------  Front End Stuff

curl -sL https://deb.nodesource.com/setup_12.x | sudo -E bash -
apt-get install -y nodejs
cd /vagrant
npm install

curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -
echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list
apt-get update && apt-get install yarn

cd /vagrant
yarn install

#--------------------------------------------  File Permissions in shared folder

chown -R vagrant /vagrant

#--------------------------------------------  Set up SSH Access

cat /home/vagrant/.ssh/me.pub >> /home/vagrant/.ssh/authorized_keys