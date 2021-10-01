FROM php:7.4
RUN apt-get update -yqq \
	&& apt-get install -yqq git libmcrypt-dev libpq-dev libcurl4-gnutls-dev libicu-dev libvpx-dev libjpeg-dev libpng-dev libxpm-dev zlib1g-dev libfreetype6-dev libxml2-dev libexpat1-dev libbz2-dev libgmp3-dev libldap2-dev unixodbc-dev libsqlite3-dev libaspell-dev libsnmp-dev libpcre3-dev libtidy-dev libzip-dev \
	&& docker-php-ext-install pdo_pgsql curl json intl gd xml zip bz2 opcache \
	&& pecl install xdebug \
 	&& pecl install mcrypt && docker-php-ext-enable mcrypt \
  	&& docker-php-ext-enable  xdebug \
  	&& curl -sS https://getcomposer.org/installer | php
