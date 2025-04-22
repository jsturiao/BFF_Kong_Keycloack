FROM php:8.2-apache

# Instalar dependências
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install zip

# Habilitar módulos Apache necessários
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

# Criar estrutura de diretórios
RUN mkdir -p /var/www/html/public/assets/css

# Copiar aplicação
WORKDIR /var/www/html
COPY public/ public/
COPY controllers/ controllers/
COPY views/ views/

# Configurar permissões
RUN chown -R www-data:www-data . \
    && chmod -R 755 .

EXPOSE 80

# Iniciar Apache com logs de erro
CMD ["apache2-foreground"]