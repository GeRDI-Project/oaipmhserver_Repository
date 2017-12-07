FROM fazy/apache-symfony 

ADD . /app


RUN /app/bin/console cache:clear
RUN /app/bin/console cache:warmup

RUN chown -R www-data:www-data /app
