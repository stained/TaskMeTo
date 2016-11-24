#!/bin/bash

#cd ${0%/*}
cd /var/taskmeto/deploy

PROJECT_NAME=taskmeto

# Add the required host entries
if [ `cat /etc/hosts | grep ${PROJECT_NAME}.local | wc -l` = 0 ]
then
    echo "192.168.33.165   ${PROJECT_NAME}.local" >> /etc/hosts
fi

# update the box
apt-get update
apt-get -y dist-upgrade

# need proper locale setting otherwise some things fail
apt-get -y install language-pack-en-base
dpkg-reconfigure locales
update-locale LC_ALL=en_US.UTF-8 LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8 LANG=en_US.UTF-8

# apache
apt-get -y install apache2

# php
apt-get -y install php5 libapache2-mod-php5 php5-mcrypt

# Composer
cd ${BASE_PROJECT_DIR}app
COMPOSER_HOME=/var/tmp/${PROJECT_NAME}/.composer
HOME=/var/tmp/${PROJECT_NAME}
curl -s https://getcomposer.org/installer | php

sudo -u www-data COMPOSER_HOME=/var/tmp/${PROJECT_NAME}/.composer HOME=/var/tmp/${PROJECT_NAME} php composer.phar self-update
sudo -u www-data COMPOSER_HOME=/var/tmp/${PROJECT_NAME}/.composer php composer.phar update

# install mysql
DEBIAN_FRONTEND=noninteractive apt-get -y install mysql-server
apt-get -y install php5-mysql
apt-get -y install php5-gd
apt-get -y install mysql-client

# import db -- as long as it's numerically sorted correctly this should do things in the correct order
cat /var/${PROJECT_NAME}/deploy/db/*.sql | mysql -u root

# copy apache config and enable it
cp /var/${PROJECT_NAME}/deploy/${PROJECT_NAME}.conf /etc/apache2/sites-available/${PROJECT_NAME}.conf
ln -s /etc/apache2/sites-available/${PROJECT_NAME}.conf /etc/apache2/sites-enabled/${PROJECT_NAME}.conf
# remove existing config
unlink /etc/apache2/sites-enabled/000-default.conf

# enable mod rewrite
ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/

# restart services
service apache2 restart

mkdir /var/${PROJECT_NAME}/upload
chmod a+w /var/${PROJECT_NAME}/upload
