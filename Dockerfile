FROM fauria/lamp 

ADD . /app
ADD util/run-lamp.sh /usr/sbin/run-lamp.sh

RUN /app/util/prepareImage.sh

RUN /app/bin/console cache:clear
RUN /app/bin/console cache:warmup

RUN chown -R www-data:www-data /app
