FROM php:7.2.1-apache-stretch
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    zip

RUN docker-php-source extract
RUN docker-php-ext-install -j$(nproc) pdo_sqlite
RUN docker-php-source delete


COPY . /var/www/

RUN sed -i 's#/usr/sbin/nologin#/bin/bash#' /etc/passwd
#RUN sed -i '#DocumentRoot /var/www/html#a SetEnv APP_ENV=prod' /etc/apache2/sites-enabled/000-default.conf
ADD app/config/config.yml.deploy /var/www/app/config/config.yml

RUN chown -R www-data:www-data /var/www

RUN /var/www/util/prepareImage.sh



