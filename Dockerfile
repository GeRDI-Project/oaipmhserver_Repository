FROM fauria/lamp 

ADD . /app

RUN /app/util/prepareImage.sh

ADD app/config/config.yml.deploy /app/app/config/config.yml

RUN /app/bin/console cache:clear
RUN /app/bin/console cache:warmup

RUN chown -R www-data:www-data /app
