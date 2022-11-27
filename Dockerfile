FROM php:8.1-cli
RUN pecl install mongodb
RUN echo extension=mongodb.so >> /usr/local/etc/php/conf.d/docker-php-ext-mongodb.ini
RUN pecl install xdebug
RUN echo zend_extension=xdebug.so >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer
RUN apt-get update && apt-get install libzip-dev -y
RUN docker-php-ext-configure zip && docker-php-ext-install zip && docker-php-ext-enable zip
ADD https://github.com/ufoscout/docker-compose-wait/releases/download/2.8.0/wait /wait
RUN chmod +x /wait
COPY . /opt/project
WORKDIR /opt/project
ENV WAIT_HOSTS=mongo:27017
ENTRYPOINT /wait && vendor/bin/phpunit
