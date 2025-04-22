FROM php:8.2-apache

# Habilitar módulos do Apache
RUN a2enmod rewrite headers

# Configurar PHP
RUN echo "display_errors=On" > /usr/local/etc/php/conf.d/display-errors.ini \
    && echo "error_reporting=E_ALL" >> /usr/local/etc/php/conf.d/display-errors.ini \
    && echo "log_errors=On" >> /usr/local/etc/php/conf.d/display-errors.ini

# Configurar Apache
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Criar diretório de logs
RUN mkdir -p /var/log/apache2 \
    && chmod 777 /var/log/apache2

# Copiar aplicação
WORKDIR /var/www/html
COPY public/ .

# Configurar permissões
RUN chown -R www-data:www-data . \
    && chmod -R 755 .

# Debug: mostrar arquivos e configurações
RUN ls -la /var/www/html \
    && apache2ctl -M \
    && apache2ctl -t \
    && cat /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]