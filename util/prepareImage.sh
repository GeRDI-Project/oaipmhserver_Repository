#!/bin/bash
# Copyright 2018 Tobias Weber weber@lrz.de
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

#sed -i 's#/var/www/html#/app/web#g' /etc/apache2/sites-available/000-default.conf
#sed -i 's#/var/www#/app/web#g' /etc/apache2/apache2.conf
rm -r /var/www/html
ln -s /var/www/web/ /var/www/html
cd /var/www
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php composer.phar install
#php composer.phar require symfony/assetic-bundle


#echo '-----installed composer-----'
#php composer.phar update
#echo '-----Updated composer-----'

#su -c '/var/www/bin/console cache:clear --env=prod' www-data
#su -c '/var/www/bin/console cache:warmup --env=prod' www-data

