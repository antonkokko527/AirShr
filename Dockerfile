#FROM tutum/apache-php
FROM nimmis/apache-php5

RUN rm -rf /etc/apache2/sites-available/* /etc/apache2/sites-enabled/*
ADD dev/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN ln /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf

# copy it in so I can get storage ownership right
#ADD . /var/www/html
#RUN chown -R www-data /var/www/html/storage
#
WORKDIR /var/laravel_storage
RUN mkdir -p views meta logs framework/views && chown -R www-data .

#RUN mkdir /var/laravel_storage && cho
WORKDIR /var/www/html
#RUN composer install

# make php magic work
RUN a2enmod rewrite

# uh make www-data root like? doesn't seem to work well.
# https://github.com/docker/docker/issues/7198
RUN usermod -u 1000 www-data

ENV LARAVEL_ENV development

ENV TZ=Australia/Sydney
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# RUN apt-get update && apt-get install -y screen
# run screen -dRR dev

DOCKER_OPTS="--dns 8.8.8.8 --dns 192.168.100.99"

EXPOSE 80

#CMD /bin/bash
