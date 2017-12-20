#!/bin/bash

apt update
apt upgrade -y
apt install mysql-server php5-mysql -y

#/etc/init.d/mysql start
apt-get remove php7.0-snmp -y
sed -i 's#/var/www/html#/app/web#g' /etc/apache2/sites-available/000-default.conf
sed -i 's#/var/www#/app/web#g' /etc/apache2/apache2.conf
ln -s /app/web /var/www/html
