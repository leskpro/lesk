FROM php:8.2-apache
COPY . /var/www/html/
RUN a2enmod rewrite
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf \
    /etc/apache2/sites-enabled/000-default.conf
RUN sed -i '/<Directory \/var\/www\/html>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' \
    /etc/apache2/apache2.conf
EXPOSE 8080
CMD ["apache2-foreground"]
