FROM oxkipo/apache2-php7
MAINTAINER Kristian Drucker

COPY . /var/www
WORKDIR /var/www
RUN composer install

ENV IMAGE_DRIVER=imagick