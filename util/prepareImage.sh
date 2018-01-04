#!/bin/bash

apt-get remove php7.0-snmp -y
apt-get install zip -y
sed -i 's#/var/www/html#/app/web#g' /etc/apache2/sites-available/000-default.conf
sed -i 's#/var/www#/app/web#g' /etc/apache2/apache2.conf
ln -s /app/web /var/www/html

cd /app
composer install