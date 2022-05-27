FROM php:8.0-apache

RUN mkdir -p /var/www/html
WORKDIR /var/www/html
COPY app .

RUN apt update
RUN apt -y install apache2
RUN apt -y install apache2-utils
RUN apt clean

RUN chown -R www-data .

RUN apt -y install libapache2-mod-evasive
COPY mod_evasive_config /etc/apache2/mods-enabled/evasive.conf

CMD ["apache2ctl", "-D", "FOREGROUND"]
