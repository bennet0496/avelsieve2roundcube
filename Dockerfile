FROM php:7.0-cli

RUN mkdir -p /build/avelsieve2roundcube
RUN docker-php-ext-install gettext
#RUN docker-php-ext-install imap

COPY . /build/avelsieve2roundcube
WORKDIR /build/avelsieve2roundcube

CMD cat phpscript.sieve | php avelsieve2roundcube.php
#CMD php test.php
