FROM php:8.2-apache

# Instalar extensão curl e outros utilitários
RUN apt-get update && \
    apt-get install -y \
    libcurl4-openssl-dev \
    apache2-utils \
    vim && \
    docker-php-ext-install curl

# Garantir que o módulo headers esteja disponível e habilitado
RUN ln -s /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/ && \
    a2enmod rewrite headers

# Configurar diretório de trabalho
WORKDIR /var/www

# Copiar arquivos
COPY public/ /var/www/html/
COPY controllers/ /var/www/controllers/
COPY views/ /var/www/views/
COPY routes/ /var/www/routes/
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Configurar permissões
RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www

# Configurar PHP
RUN echo "error_reporting=E_ALL" > /usr/local/etc/php/conf.d/error-reporting.ini && \
    echo "display_errors=On" >> /usr/local/etc/php/conf.d/error-reporting.ini && \
    echo "log_errors=On" >> /usr/local/etc/php/conf.d/error-reporting.ini

# Verificar configuração
RUN apache2ctl -M && \
    apache2ctl configtest

EXPOSE 80

CMD ["apache2-foreground"]