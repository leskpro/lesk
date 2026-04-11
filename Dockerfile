FROM php:8.2-apache
COPY index.php /var/www/html/index.php
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf \
    /etc/apache2/sites-enabled/000-default.conf
EXPOSE 8080
CMD ["apache2-foreground"]