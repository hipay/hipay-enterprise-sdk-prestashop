FROM php:5.4-apache

COPY . /var/www/html

RUN apt-get update && apt-get install -y \
	vim \
	git \
	npm

# Date
RUN echo "date.timezone = Europe/Paris" > /usr/local/etc/php/conf.d/date.ini

